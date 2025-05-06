<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActiveUserMiddleware
{
    /**
     * Xử lý request, chỉ cho phép user active truy cập.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('api')->user(); // Lấy thông tin user đang đăng nhập
        if (!$user || !$user->is_active) { // Nếu user không tồn tại hoặc không active
            return response()->json(['error' => 'Tài khoản của bạn đã bị khóa hoặc chưa active!'], 403);
        }
        return $next($request); // Cho phép truy cập
    }
}