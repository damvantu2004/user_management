<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\PasswordResetService;

/**
 * Controller xử lý các chức năng liên quan đến đặt lại mật khẩu
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and password reset endpoints"
 * )
 */
class PasswordResetController extends Controller
{
    /**
     * Service xử lý logic đặt lại mật khẩu
     */
    protected $passwordResetService;

    /**
     * Khởi tạo controller với dependency injection
     */
    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * Gửi email chứa token đặt lại mật khẩu
     * 
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
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Validate đầu vào
        // validate dữ liệu đầu vào 
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        // phát hiện lỗi không có email thì trả vẻ response luôn tránh lỗi 500
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        try {
            // Gọi service để xử lý logic gửi email
            $result = $this->passwordResetService->sendResetLink($request->email);
            return $this->successResponse(null, $result['message']);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Đặt lại mật khẩu bằng token
     * 
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
     *     )
     * )
     */
    public function reset(Request $request)
    {
        // Validate đầu vào với các quy tắc cụ thể
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Trả về lỗi nếu validate thất bại
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            // Gọi service để xử lý logic đặt lại mật khẩu
            $result = $this->passwordResetService->resetPassword($request->all());
            return $this->successResponse(null, $result['message']);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
