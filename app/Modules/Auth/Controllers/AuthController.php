<?php

namespace App\Modules\Auth\Controllers;

use Illuminate\Http\Request;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Các API liên quan đến xác thực người dùng"
 * )
 */
class AuthController extends Controller
{
    protected $authService;

    // Bật lại captcha validation
    private const VALIDATION_RULES = [
        'register' => [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|in:patient,doctor',
            'captcha' => 'required|string', // Bật lại captcha
        ],
        'login' => [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'captcha' => 'required|string', // Bật lại captcha
            'remember' => 'sometimes|boolean',
        ],
        'verify_email' => [
            'email' => 'required|string|email',
            'token' => 'required|string',
        ]
    ];

    public function __construct(AuthService $authService) // hàm khởi tạo controller
    {
        $this->authService = $authService; // gán authService vào biến authService
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verifyEmail']]); // khởi tạo controller với dependency injection để sử dụng authService trong controller
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Đăng ký tài khoản mới",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Đăng ký thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), self::VALIDATION_RULES['register']);

            if ($validator->fails()) {
                return $this->errorResponse('Dữ liệu không hợp lệ', 422, $validator->errors());
            }

            $data = $validator->validated();
            
            // Thêm logic xác định role
            $role = $request->input('role', 'patient'); // Mặc định là patient
            if (!in_array($role, ['patient', 'doctor'])) {
                $role = 'patient';
            }
            
            $data['role'] = $role;

            // Bật lại kiểm tra captcha
            if (!$this->verifyCaptcha($request->input('captcha'))) {
                return $this->errorResponse('Captcha không hợp lệ', 422);
            }

            $result = $this->authService->register($data);
            return $this->successResponse($result, 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.', 201);
        } catch (\Exception $e) {
            Log::error('Registration failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('Có lỗi xảy ra trong quá trình đăng ký', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/verify-email",
     *     summary="Xác thực email",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","token"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xác thực thành công"
     *     )
     * )
     */
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), self::VALIDATION_RULES['verify_email']);
        Log::info($request->all());
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $result = $this->authService->verifyEmail($request->all());
        return view('auth.verification_success', ['message' => $result['message']]);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Đăng nhập",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="remember", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đăng nhập thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string"),
     *             @OA\Property(property="expires_in", type="integer"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), self::VALIDATION_RULES['login']);

            if ($validator->fails()) {
                return $this->errorResponse('Thông tin không hợp lệ', 422, $validator->errors());
            }

            // Bật lại kiểm tra captcha
            if (!$this->verifyCaptcha($request->input('captcha'))) {
                return $this->errorResponse('Captcha không hợp lệ', 422);
            }

            $remember = $request->boolean('remember', false);
            $result = $this->authService->login($request->only(['email', 'password']), $remember);

            return $this->successResponse([
                'access_token' => $result['access_token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
                'user' => $result['user']
            ], $result['message']);
        } catch (ValidationException $e) {
            return $this->errorResponse('Đăng nhập thất bại', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Đăng xuất",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Đăng xuất thành công"
     *     )
     * )
     */
    public function logout()
    {
        $result = $this->authService->logout();
        return $this->successResponse(null, $result['message']);
    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Làm mới token",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token đã được làm mới"
     *     )
     * )
     */
    public function refresh()
    {
        $result = $this->authService->refresh();
        return $this->successResponse([
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
            'user' => $result['user']
        ], 'Token đã được làm mới');
    }

    /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Lấy thông tin người dùng hiện tại",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin người dùng"
     *     )
     * )
     */
    public function me()
    {
        $user = $this->authService->me();
        return $this->successResponse($user, 'Thông tin người dùng hiện tại');
    }

    protected function verifyCaptcha($captchaToken)
    {
        // Đặt đây là secret key reCAPTCHA phía server (tạo ở Google reCAPTCHA site)
        $secret = env('GOOGLE_RECAPTCHA_SECRET'); // lấy từ env
        if (empty($secret) || empty($captchaToken)) return false; // nếu secret hoặc captchaToken rỗng thì trả về false

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [ // đây là url xác thực reCAPTCHA
            'secret' => $secret,
            'response' => $captchaToken, // token được trả về từ reCAPTCHA
            // 'remoteip'=> request()->ip(), // tùy nếu muốn check IP
        ]);

        $json = $response->json();

        return $json['success'] ?? false;
    }
}





