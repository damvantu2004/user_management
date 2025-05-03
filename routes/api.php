<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordResetController;

/*
|--------------------------------------------------------------------------
| Routes công khai (Public Routes)
|--------------------------------------------------------------------------
| Những routes này có thể truy cập mà không cần đăng nhập
| Bao gồm: đăng ký, đăng nhập, xác thực email, quên mật khẩu
*/

Route::post('register', [AuthController::class, 'register']); // Đăng ký tài khoản mới
Route::get('verify-email', [AuthController::class, 'verifyEmail']); // Xác thực email
Route::post('login', [AuthController::class, 'login']); // Đăng nhập
Route::post('password/forgot', [PasswordResetController::class, 'sendResetLinkEmail']); // Gửi email quên mật khẩu
Route::post('password/reset', [PasswordResetController::class, 'reset']); // Đặt lại mật khẩu

/*
|--------------------------------------------------------------------------
| Routes yêu cầu xác thực (Protected Routes)
|--------------------------------------------------------------------------
| Những routes này yêu cầu người dùng phải đăng nhập và tài khoản phải active
| Sử dụng 2 middleware:
| - auth:api: Kiểm tra JWT token hợp lệ
| - active: Kiểm tra tài khoản đã được kích hoạt
*/
Route::middleware(['auth:api', 'active'])->group(function () {
    // Quản lý xác thực
    Route::post('logout', [AuthController::class, 'logout']); // Đăng xuất
    Route::post('refresh', [AuthController::class, 'refresh']); // Làm mới token JWT
    Route::get('me', [AuthController::class, 'me']); // Lấy thông tin người dùng hiện tại
});

/*
|--------------------------------------------------------------------------
| Routes dành cho Admin
|--------------------------------------------------------------------------
| Những routes này chỉ admin mới có quyền truy cập
| Sử dụng 3 middleware:
| - auth:api: Kiểm tra JWT token hợp lệ
| - active: Kiểm tra tài khoản đã được kích hoạt
| - admin: Kiểm tra người dùng có quyền admin
*/
Route::middleware(['auth:api', 'active', 'admin'])->group(function () {
    // Quản lý người dùng (chỉ admin mới có quyền)
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']); // Lấy danh sách tất cả người dùng
        Route::post('/', [UserController::class, 'store']); // Lấy danh sách tất cả người dùng
        Route::get('/{id}', [UserController::class, 'show']); // Xem chi tiết một người dùng
        Route::put('/{id}', [UserController::class, 'update']); // Cập nhật thông tin người dùng
        Route::delete('/{id}', [UserController::class, 'destroy']); // Xóa người dùng
    });
});

/*
|--------------------------------------------------------------------------
| Route mặc định của Laravel Sanctum
|--------------------------------------------------------------------------
| Route này không cần thiết vì chúng ta đang sử dụng JWT
| Có thể xóa hoặc comment lại
*/
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
