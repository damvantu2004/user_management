<?php

namespace App\Modules\Auth\Services;

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
                'role' => $data['role'],
                'is_active' => false,
                'email_verified_at' => null,
            ]);

            // Tự động tạo Patient hoặc Doctor record
            if ($data['role'] === 'patient') {
                \App\Modules\Patient\Models\Patient::create([
                    'user_id' => $user->id,
                ]);
            } elseif ($data['role'] === 'doctor') {
                \App\Modules\Doctor\Models\Doctor::create([
                    'user_id' => $user->id,
                    'specialty' => 'Chuyên khoa chung',
                    'qualification' => 'Cần cập nhật',
                    'experience_years' => 0,
                    'consultation_fee' => 0,
                    'is_available' => false, // Admin sẽ kích hoạt sau
                ]);
            }

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
                'email' => $user->email,
                'role' => $user->role
            ]);

            return [
                'user' => $user,
                'message' => 'Đăng ký thành công'
            ];
        });
    }

    public function verifyEmail(array $data): array
    {
        $key = 'verify_email_' . $data['email']; // tạo key để lưu vào bộ đếm giới hạn số lần xác thực email

        if (RateLimiter::tooManyAttempts($key, self::MAX_VERIFY_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ['Quá nhiều lần thử. Vui lòng đợi ' . ceil($seconds / 60) . ' phút.']
            ]);
        }

        $verify = DB::table('email_verification_tokens') // lấy thông tin token từ database
            ->where('email', $data['email'])
            ->first(); // dòng đầu tiên tìm thấy

        if (!$verify) { // nếu không tìm thấy token
            RateLimiter::hit($key); // tăng số lần thử lên 1
            throw ValidationException::withMessages([
                'token' => ['Token không hợp lệ hoặc đã hết hạn']
            ]);
        }

        if (now()->diffInMinutes($verify->created_at) > self::EMAIL_TOKEN_EXPIRY) { // nếu token hết hạn
            DB::table('email_verification_tokens')->where('email', $data['email'])->delete(); // nếu token hết hạn thì xóa bản ghi khỏi database
            throw ValidationException::withMessages([
                'token' => ['Token đã hết hạn. Vui lòng đăng ký lại.']
            ]);
        }

        if (!Hash::check($data['token'], $verify->token)) { //nếu hash token truyền vào không khớp với verify->token là token được lưu trong database thì ! để đảo ngược giá trị của nó thành true
            RateLimiter::hit($key); // tăng số lần thử lên 1
            throw ValidationException::withMessages([
                'token' => ['Token không hợp lệ']
            ]);
        }
        // nếu vượt qua các trường hợp trên thì token là hợp lệ

        $user = User::where('email', $data['email'])->first(); // tìm kiếm user có email trùng với email trong verify
        $user->email_verified_at = now(); // cập nhật email_verified_at thành thời điểm hiện tại
        $user->is_active = true; // cập nhật is_active thành true
        $user->save(); // lưu lại vào database

        DB::table('email_verification_tokens')->where('email', $data['email'])->delete(); // xóa token khỏi database
        RateLimiter::clear($key); // xóa bộ đếm giới hạn số lần xác thực email

        Log::info('Email verified', ['user_id' => $user->id]);

        return [
            'message' => 'Xác thực email thành công! Bạn có thể đăng nhập.'
        ];
    }

    public function login(array $credentials, bool $remember = false): array // mặc định remember = false
    {
        $this->checkTooManyAttempts($credentials['email']);
        $this->setTokenLifetime($remember); // thiết lập thời gian sống của token

        if (!$token = Auth::guard('api')->attempt($credentials)) { // tạo token bằng cách gọi đến xác thực, nếu thành công sẽ gán token vào biến $token, nếu token là false thì sẽ trả về lỗi. attempt dùng để xác thực đăng nhập bằng cách so sánh email và password trong database
            RateLimiter::hit($this->throttleKey($credentials['email'])); // tăng số lần thử lên 1
            Log::warning('Failed login attempt', ['email' => $credentials['email']]);
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không đúng'],
            ]);
        }

        RateLimiter::clear($this->throttleKey($credentials['email'])); // xóa bộ đếm giới hạn số lần đăng nhập

        $user = Auth::guard('api')->user(); //Auth::guard('api'): Truy cập guard API được cấu hình với driver JWT trong  config/auth.php, Truy vấn database để lấy đối tượng User dựa trên ID, gán user vào biến $user
        $this->validateUserStatus($user);  // kiểm tra trạng thái user xem đã active và xác thực email chưa

        Log::info('User logged in', [
            'user_id' => $user->id,
            'remember' => $remember,
            'ttl' => config('jwt.ttl') 
        ]);

        return $this->generateTokenResponse($token, $user, $remember); // trả về token với các tham só truyền vào
    }

    public function logout(): array
    {
        $user = Auth::guard('api')->user();
        Auth::guard('api')->logout(); // gọi phương thức logout của guard api để đăng xuất

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
            $seconds = RateLimiter::availableIn($this->throttleKey($email)); // availableIn: Trả về số giây còn lại cho phép thử lại
            throw ValidationException::withMessages([
                'email' => ['Quá nhiều lần đăng nhập thất bại. Vui lòng thử lại sau ' . ceil($seconds ) . ' giây.'],
            ]);
        }
    }

    private function throttleKey(string $email): string
    {
        return strtolower($email) . '|' . request()->ip();  // chặn theo cùng lúc email và ip
        //return strtolower($email); //chỉ chặn theo email
        //return strtolower (request()->ip()); // chỉ chặn theo ip
    }

    private function setTokenLifetime(bool $remember): void
    {
        $ttl = $remember
            ? config('jwt.refresh_ttl') // nếu remember là true thì thiết lập thời gian sống của token là refresh_ttl
            : config('jwt.ttl');        // nếu remember là false thì thiết lập thời gian sống của token là ttl

        Auth::guard('api')->factory()->setTTL($ttl);
    }

    private function validateUserStatus(User $user): void
    {
        if (!$user->is_active || !$user->email_verified_at) { // nếu user false active hoặc false email_verified_at thì điều kiện trong if là true
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
