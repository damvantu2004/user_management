<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request; 

define('LARAVEL_START', microtime(true));   // định nghĩa hằng số LARAVEL_START với thời gian hiện tại

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) { // dùng  file maintenance.php để kiểm tra trạng thái bảo trì của ứng dụng
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/
// tải file autoload.php từ thư mục vendor. file này dùng để tải các thư viện và các lớp của ứng dụng
require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php'; // thực hiện các thiết lập ban đầu của ứng dụng từ file bootstrap/app.php
// tạo kernel dùng để xử lý request HTTP
$kernel = $app->make(Kernel::class); // lấy instance của HTTP Kernel từ container đăng ký trong file bootstrap/app.php

$response = $kernel->handle( // HTTP kernel xử lý request thông qua phương thức handle(), thực hiện các middleware và định tuyến request đến controller thích hợp
    $request = Request::capture() // dùng để bắt request HTTP hiện tại, gán vào biến $request

)->send(); //   response được gửi trả về client

$kernel->terminate($request, $response); // sau khi gửi response, kernel sẽ thực hiện các hành động cuối cùng là terminate() để  Chạy các phương thức terminate của middleware.
//Dọn dẹp tài nguyên sau khi request hoàn tất.
//Đảm bảo vòng đời request được kết thúc đúng cách.
