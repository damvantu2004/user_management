<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Mail\ResetPasswordMail;

class PasswordResetService
{
    /**
     * Độ dài của token đặt lại mật khẩu
     */
    private const TOKEN_LENGTH = 60;

    /**
     * Gửi email chứa token đặt lại mật khẩu
     * 
     * @param string $email Email của người dùng
     * @throws \Exception Khi email chưa xác thực hoặc có lỗi xảy ra
     * @return array Mảng chứa thông báo kết quả
     */
    public function sendResetLink(string $email): array
    {
        // Kiểm tra user có tồn tại và đã xác thực email chưa
        $user = User::where('email', $email)->first();
        if (!$user || !$user->email_verified_at) {
            throw new \Exception('Email không hợp lệ', 400);
        }

        // Tạo token ngẫu nhiên và hash token
        $token = Str::random(self::TOKEN_LENGTH);
        $hashedToken = Hash::make($token);

        // Lưu token đã hash vào database
        // Nếu email đã tồn tại thì cập nhật token mới
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $hashedToken,
                'created_at' => Carbon::now()
            ]
        );

        // Gửi email chứa token gốc (chưa hash) cho người dùng
        Mail::to($email)->send(new ResetPasswordMail($email, $token));

        return [
            'message' => 'Đã gửi email đặt lại mật khẩu!'
        ];
    }

    /**
     * Đặt lại mật khẩu bằng token
     * 
     * @param array $data Dữ liệu đặt lại mật khẩu (email, token, password)
     * @throws \Exception Khi token không hợp lệ hoặc có lỗi xảy ra
     * @return array Mảng chứa thông báo kết quả
     */
    public function resetPassword(array $data): array
    {
        // Lấy thông tin token từ database
        $reset = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->first();

        // Kiểm tra token có tồn tại và hợp lệ không
        if (!$reset || !Hash::check($data['token'], $reset->token)) {
            throw new \Exception('Token không hợp lệ hoặc đã hết hạn', 400);
        }

        // Cập nhật mật khẩu mới
        // Password sẽ tự động được hash nhờ $casts trong Model User
        $user = User::where('email', $data['email'])->first();
        $user->password = $data['password'];
        $user->save();

        // Xóa token sau khi đã sử dụng thành công
        DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->delete();

        return [
            'message' => 'Đặt lại mật khẩu thành công!'
        ];
    }
}
