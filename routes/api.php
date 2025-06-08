<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Auth\Controllers\PasswordResetController;
use App\Modules\Patient\Controllers\PatientController;
use App\Modules\Doctor\Controllers\DoctorController;
use App\Modules\Appointment\Controllers\AppointmentController;
use App\Http\Controllers\UserController;

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
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('doctors', [DoctorController::class, 'index']);
Route::get('doctors/{id}', [DoctorController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Protected Routes - Require Authentication
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'active'])->group(function () {
    // Auth management
    Route::post('logout', [AuthController::class, 'logout']); // Đăng xuất
    Route::post('refresh', [AuthController::class, 'refresh']); // Làm mới token JWT
    Route::get('me', [AuthController::class, 'me']); // Lấy thông tin người dùng hiện tại

    /*
    |--------------------------------------------------------------------------
    | Patient Only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['patient'])->group(function () {
        // Patient profile management
        Route::get('patients/me', [PatientController::class, 'profile']);
        Route::put('patients/me', [PatientController::class, 'updateProfile']);
        
        // Patient appointment management
        Route::post('appointments', [AppointmentController::class, 'store']); // Đặt lịch
    });

    /*
    |--------------------------------------------------------------------------
    | Doctor Only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['doctor'])->group(function () {
        // Doctor view patients
        Route::get('patients', [PatientController::class, 'index']);
        Route::get('patients/{id}', [PatientController::class, 'show']);
        
        // Doctor manage appointments
        Route::put('appointments/{id}', [AppointmentController::class, 'update']); // Xác nhận lịch
    });

    /*
    |--------------------------------------------------------------------------
    | Both Patient & Doctor Routes
    |--------------------------------------------------------------------------
    */
    // View appointments (filtered by role)
    Route::get('appointments', [AppointmentController::class, 'index']);
    Route::get('appointments/{id}', [AppointmentController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Admin Only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin'])->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
        });
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
