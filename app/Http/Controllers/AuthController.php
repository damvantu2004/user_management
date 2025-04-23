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

/**
 * @OA\Info(
 *     title="User Management API",
 *     version="1.0.0",
 *     description="API for user registration, authentication and profile management."
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Nguyễn Văn A"),
 *     @OA\Property(property="email", type="string", example="user@example.com"),
 *     @OA\Property(property="role", type="string", example="user"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time")
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Đăng ký tài khoản mới, gửi mail xác thực email",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Nguyễn Văn A"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Đăng ký thành công"),
     *     @OA\Response(response=422, description="Lỗi validate")
     * )
     */
    public function register(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', // Mật khẩu phải có ít nhất 6 ký tự và xác nhận
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
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

        return $this->createdResponse($user, 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.');
    }

    /**
     * @OA\Get(
     *     path="/api/verify-email",
     *     summary="Xác thực email từ link gửi về mail",
     *     tags={"Authentication"},
     *     @OA\Parameter(name="email", in="query", required=true, @OA\Schema(type="string", format="email")),
     *     @OA\Parameter(name="token", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Xác thực thành công"),
     *     @OA\Response(response=400, description="Token không hợp lệ hoặc hết hạn")
     * )
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
            return $this->errorResponse('Token không hợp lệ hoặc đã hết hạn', 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->email_verified_at = now();
        $user->is_active = true;
        $user->save();

        DB::table('email_verification_tokens')->where('email', $request->email)->delete();

        return $this->successResponse(null, 'Xác thực email thành công! Bạn có thể đăng nhập.');
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Đăng nhập và trả về JWT token (chỉ cho user đã xác thực email)",
     *     description="Hỗ trợ nhớ đăng nhập bằng cách tăng thời gian sống của token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="remember", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Đăng nhập thành công"),
     *     @OA\Response(response=401, description="Email hoặc mật khẩu không đúng"),
     *     @OA\Response(response=403, description="Tài khoản chưa xác thực email hoặc đã bị khóa")
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember' => 'sometimes|boolean', // Trường remember là tùy chọn, kiểu boolean
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $credentials = $request->only(['email', 'password']);
        $remember = $request->boolean('remember', false); // Mặc định là false nếu không có

        // Đặt thời gian sống của token tùy theo remember
        if ($remember) {
            // Thời gian dài cho "Nhớ đăng nhập" (30 ngày)
            Auth::guard('api')->factory()->setTTL(60 * 24 * 30); // 60 phút * 24 giờ * 30 ngày
        } else {
            // Thời gian mặc định cho phiên thông thường (1 giờ)
            Auth::guard('api')->factory()->setTTL(60);
        }

        // Thực hiện đăng nhập
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return $this->unauthorizedResponse('Email hoặc mật khẩu không đúng');
        }

        // Kiểm tra trạng thái tài khoản
        $user = Auth::guard('api')->user();
        if (!$user->is_active || !$user->email_verified_at) {
            Auth::guard('api')->logout(); // Đăng xuất để hủy token
            return $this->forbiddenResponse('Tài khoản chưa xác thực email hoặc đã bị khóa');
        }

        return $this->respondWithToken($token, $remember);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Đăng xuất (hủy token hiện tại)",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Đăng xuất thành công")
     * )
     */
    public function logout()
    {
        Auth::guard('api')->logout(); // Gọi phương thức logout() của JWT guard
        return $this->successResponse(null, 'Đăng xuất thành công');
    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Làm mới JWT token",
     *     description="Giữ nguyên thời gian sống của token ban đầu",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token đã được làm mới",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function refresh()
    {
        $token = Auth::guard('api')->refresh(); // Gọi phương thức refresh() của JWT guard
        $user = Auth::guard('api')->user();
        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => $user
        ];
        
        return $this->successResponse($data, 'Token đã được làm mới');
    }

    /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Lấy thông tin user hiện tại (yêu cầu xác thực)",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Trả về thông tin user",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     )
     * )
     */
    public function me()
    {
        $user = Auth::guard('api')->user();
        return $this->successResponse($user, 'Thông tin người dùng hiện tại');
    }

    /**
     * Trả về response chuẩn với JWT token.
     * 
     * @param string $token JWT token
     * @param bool $remember Có phải đăng nhập với chế độ ghi nhớ không
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $remember = false)
    {
        $user = Auth::guard('api')->user();
        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => $user // Trả thêm thông tin user để tiện dụng
        ];
        
        return $this->successResponse($data, 'Đăng nhập thành công' . ($remember ? ' với chế độ ghi nhớ' : ''));
    }
}


