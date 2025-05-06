<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [ // các middleware được sử dụng trong ứng dụng
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class, // middleware để xác thực proxy
        \Illuminate\Http\Middleware\HandleCors::class, // middleware để xử lý CORS
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class, // middleware để ngăn chặn request khi ứng dụng đang trong chế độ bảo trì
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class, // middleware để kiểm tra kích thước của request POST
        \App\Http\Middleware\TrimStrings::class, // middleware để cắt bỏ khoảng trắng ở đầu và cuối các chuỗi trong request
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class, // middleware để chuyển chuỗi rỗng thành null
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class, // middleware để mã hóa cookie
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class, // middleware để thêm cookie vào response
            \Illuminate\Session\Middleware\StartSession::class, // middleware để bắt đầu session
            \Illuminate\View\Middleware\ShareErrorsFromSession::class, // middleware để chia sẻ lỗi từ session
            \App\Http\Middleware\VerifyCsrfToken::class, // middleware để xác thực token CSRF
            \Illuminate\Routing\Middleware\SubstituteBindings::class, // middleware để thay thế các ràng buộc
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api', // middleware để giới hạn số lượng request
            \Illuminate\Routing\Middleware\SubstituteBindings::class, // middleware để thay thế các ràng buộc
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [ // các bí danh của middleware dùng để dễ dàng gán middleware cho các route và nhóm route
        'auth' => \App\Http\Middleware\Authenticate::class, 
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'admin' => \App\Http\Middleware\AdminRoleMiddleware::class,
        'active' => \App\Http\Middleware\ActiveUserMiddleware::class,
    ];
}
