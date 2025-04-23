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
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and password reset endpoints"
 * )
 */
class PasswordResetController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/password/email",
     *     summary="Gửi email chứa token đặt lại mật khẩu",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đã gửi email đặt lại mật khẩu",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đã gửi email đặt lại mật khẩu!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi yêu cầu (email chưa xác thực hoặc không tồn tại)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Email chưa được xác thực!")
     *         )
     *     )
     * )
     * Gửi email chứa token đặt lại mật khẩu cho user (chỉ cho user đã xác thực email).
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !$user->email_verified_at) {
            return $this->errorResponse('Email chưa được xác thực!', 400);
        }

        // Tạo token ngẫu nhiên
        $token = Str::random(60);
        $hashedToken = Hash::make($token);

        // Lưu hashed token vào DB
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $hashedToken,
                'created_at' => Carbon::now()
            ]
        );

        // Gửi token gốc (chưa hash) qua email
        Mail::to($request->email)->send(new ResetPasswordMail($request->email, $token));

        return $this->successResponse(null, 'Đã gửi email đặt lại mật khẩu!');
    }

    /**
     * @OA\Post(
     *     path="/api/password/reset",
     *     summary="Đặt lại mật khẩu bằng token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","token","password","password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="the-reset-token"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đặt lại mật khẩu thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đặt lại mật khẩu thành công!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token không hợp lệ hoặc hết hạn",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token không hợp lệ hoặc đã hết hạn")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi validate dữ liệu",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Lỗi validate"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="field_name",
     *                     type="array",
     *                     @OA\Items(type="string", example="Lỗi cụ thể")
     *                 )
     *             )
     *         )
     *     )
     * )
     * Đặt lại mật khẩu bằng token.
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Lấy record với token đã hash
        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        // So sánh token gửi lên với token đã hash trong DB
        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return $this->errorResponse('Token không hợp lệ hoặc đã hết hạn', 400);
        }

        // Cập nhật mật khẩu (sẽ tự động hash nhờ $casts trong Model)
        $user = User::where('email', $request->email)->first();
        $user->password = $request->password;
        $user->save();

        // Xóa token sau khi dùng
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return $this->successResponse(null, 'Đặt lại mật khẩu thành công!');
    }
}






