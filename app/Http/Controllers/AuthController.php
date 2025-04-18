<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\VerifyEmailMail;

class AuthController extends Controller
{
    /**
     * Đăng ký tài khoản mới, gửi mail xác thực email.
     */
    public function register(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Tạo user mới, chưa active, chưa xác thực email
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // Mật khẩu sẽ tự động hash do $casts trong Model
            'role' => 'user',
            'is_active' => false,
            'email_verified_at' => null,
        ]);

        // Tạo token xác thực email
        $token = Str::random(60);
        DB::table('email_verification_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => now()
            ]
        );

        // Gửi mail xác thực
        Mail::to($request->email)->send(new VerifyEmailMail($request->email, $token));

        return response()->json([
            'message' => 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.',
            'user' => $user
        ], 201);
    }

    /**
     * Xác thực email từ link gửi về mail.
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
        ]);

        $verify = DB::table('email_verification_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$verify) {
            return response()->json(['error' => 'Token không hợp lệ hoặc đã hết hạn'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->email_verified_at = now();
        $user->is_active = true;
        $user->save();

        DB::table('email_verification_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Xác thực email thành công! Bạn có thể đăng nhập.']);
    }

    /**
     * Đăng nhập và trả về JWT token (chỉ cho user đã xác thực email).
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Email hoặc mật khẩu không đúng'], 401);
        }

        $user = Auth::guard('api')->user();
        if (!$user->is_active || !$user->email_verified_at) {
            return response()->json(['error' => 'Tài khoản chưa xác thực email hoặc đã bị khóa'], 403);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Đăng xuất (hủy token hiện tại).
     */
    public function logout()
    {
        Auth::guard('api')->logout();
        return response()->json(['message' => 'Đăng xuất thành công']);
    }

    /**
     * Làm mới JWT token.
     */
    public function refresh()
    {
        $token = Auth::guard('api')->refresh();
        return $this->respondWithToken($token);
    }

    /**
     * Lấy thông tin user hiện tại (yêu cầu xác thực).
     */
    public function me()
    {
        return response()->json(Auth::guard('api')->user());
    }

    /**
     * Trả về response chuẩn với JWT token.
     */
    protected function respondWithToken($token) // $token là JWT token
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }
}
