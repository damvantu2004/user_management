<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/
    // tạo một instance của ứng dụng Laravel, nhận vào một tham số là đường dẫn đến thư mục gốc của ứng dụng
$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton( // tạo một instance duy nhất cho ứng dụng với key là Illuminate\Contracts\Http\Kernel::class, và value là App\Http\Kernel::class
    // khi cần sử dụng, ta có thể lấy instance này ra từ container bằng cách gọi $app->make(Illuminate\Contracts\Http\Kernel::class)
    // ví dụ: $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton( // tạo một instance duy nhất cho ứng dụng với key là Illuminate\Contracts\Console\Kernel::class, và value là App\Console\Kernel::class
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton( // tạo một instance duy nhất cho ứng dụng với key là Illuminate\Contracts\Debug\ExceptionHandler::class, và value là App\Exceptions\Handler::class
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
