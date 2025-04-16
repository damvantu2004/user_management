<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Mail\ResetPasswordMail;

class PasswordResetController extends Controller
{
    /**
     * Gửi email chứa token đặt lại mật khẩu cho user (chỉ cho user đã xác thực email).
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Validate email
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !$user->email_verified_at) {
            return response()->json(['error' => 'Email chưa được xác thực!'], 400);
        }

        // Tạo token ngẫu nhiên
        $token = Str::random(60);

        // Lưu token vào bảng password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        // Gửi email sử dụng Mailable
        Mail::to($request->email)->send(new ResetPasswordMail($request->email, $token));

        return response()->json(['message' => 'Đã gửi email đặt lại mật khẩu!']);
    }

    /**
     * Đặt lại mật khẩu bằng token.
     */
    public function reset(Request $request)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Kiểm tra token hợp lệ
        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return response()->json(['error' => 'Token không hợp lệ hoặc đã hết hạn'], 400);
        }

        // Đặt lại mật khẩu cho user
        $user = User::where('email', $request->email)->first();
        $user->password = $request->password; // Laravel 10 sẽ tự hash nhờ $casts
        $user->save();

        // Xóa token sau khi dùng
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Đặt lại mật khẩu thành công!']);
    }
}
