<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\VerifyEmailMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthService
{
    private const EMAIL_TOKEN_LENGTH = 32;
    private const EMAIL_TOKEN_EXPIRY = 60; // 1 hour in minutes
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const MAX_VERIFY_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 15; // minutes

    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'user',
                'is_active' => false,
                'email_verified_at' => null,
            ]);

            // Generate and hash verification token
            $token = Str::random(self::EMAIL_TOKEN_LENGTH);
            $hashedToken = Hash::make($token);

            DB::table('email_verification_tokens')->updateOrInsert(
                ['email' => $data['email']],
                [
                    'token' => $hashedToken,
                    'created_at' => now()
                ]
            );

            Mail::to($data['email'])->send(new VerifyEmailMail($data['email'], $token));

            Log::info('New user registered', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return [
                'user' => $user,
                'message' => 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.'
            ];
        });
    }

    public function verifyEmail(array $data): array
    {
        $key = 'verify_email_' . $data['email'];

        if (RateLimiter::tooManyAttempts($key, self::MAX_VERIFY_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ['Quá nhiều lần thử. Vui lòng đợi ' . ceil($seconds / 60) . ' phút.']
            ]);
        }

        $verify = DB::table('email_verification_tokens')
            ->where('email', $data['email'])
            ->first();

        if (!$verify) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages([
                'token' => ['Token không hợp lệ hoặc đã hết hạn']
            ]);
        }

        if (now()->diffInMinutes($verify->created_at) > self::EMAIL_TOKEN_EXPIRY) {
            DB::table('email_verification_tokens')->where('email', $data['email'])->delete();
            throw ValidationException::withMessages([
                'token' => ['Token đã hết hạn. Vui lòng đăng ký lại.']
            ]);
        }

        if (!Hash::check($data['token'], $verify->token)) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages([
                'token' => ['Token không hợp lệ']
            ]);
        }

        $user = User::where('email', $data['email'])->first();
        $user->email_verified_at = now();
        $user->is_active = true;
        $user->save();

        DB::table('email_verification_tokens')->where('email', $data['email'])->delete();
        RateLimiter::clear($key);

        Log::info('Email verified', ['user_id' => $user->id]);

        return [
            'message' => 'Xác thực email thành công! Bạn có thể đăng nhập.'
        ];
    }

    public function login(array $credentials, bool $remember = false): array
    {
        $this->checkTooManyAttempts($credentials['email']); 
        $this->setTokenLifetime($remember);

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            RateLimiter::hit($this->throttleKey($credentials['email']));
            Log::warning('Failed login attempt', ['email' => $credentials['email']]);
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không đúng'],
            ]);
        }

        RateLimiter::clear($this->throttleKey($credentials['email']));

        $user = Auth::guard('api')->user();
        $this->validateUserStatus($user);

        Log::info('User logged in', [
            'user_id' => $user->id,
            'remember' => $remember,
            'ttl' => config('jwt.ttl') //   thời gian sống của token
        ]);

        return $this->generateTokenResponse($token, $user, $remember);
    }

    public function logout(): array
    {
        $user = Auth::guard('api')->user();
        Auth::guard('api')->logout();

        Log::info('User logged out', ['user_id' => $user->id]);

        return [
            'message' => 'Đăng xuất thành công'
        ];
    }

    public function refresh(): array
    {
        $token = Auth::guard('api')->refresh();
        $user = Auth::guard('api')->user();

        Log::info('Token refreshed', ['user_id' => $user->id]);

        return $this->generateTokenResponse($token, $user);
    }

    public function me(): User
    {
        return Auth::guard('api')->user();
    }

    private function checkTooManyAttempts(string $email): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($email), self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($email));
            throw ValidationException::withMessages([
                'email' => ['Quá nhiều lần đăng nhập thất bại. Vui lòng thử lại sau ' . ceil($seconds / 60) . ' phút.'],
            ]);
        }
    }

    private function throttleKey(string $email): string
    {
        return strtolower($email) . '|' . request()->ip();  // chặn theo email và ip
        //return strtolower($email); //chỉ chặn theo email
        //return strtolower (request()->ip()); // chỉ chặn theo ip
    }

    private function setTokenLifetime(bool $remember): void
    {
        $ttl = $remember
            ? config('jwt.refresh_ttl')
            : config('jwt.ttl');

        Auth::guard('api')->factory()->setTTL($ttl);
    }

    private function validateUserStatus(User $user): void
    {
        if (!$user->is_active || !$user->email_verified_at) {
            Auth::guard('api')->logout();
            throw ValidationException::withMessages([
                'email' => ['Tài khoản chưa xác thực email hoặc đã bị khóa'],
            ]);
        }
    }

    private function generateTokenResponse(string $token, User $user, bool $remember = false): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => $user,
            'message' => 'Đăng nhập thành công' . ($remember ? ' với chế độ ghi nhớ' : '')
        ];
    }
}
