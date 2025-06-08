<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRoleMiddleware
{
    /**
     * Xử lý request, chỉ cho phép admin truy cập.
     */

    public function handle(Request $request, Closure $next) // Closure là một hàm callback, next là một hàm callback được truyền vào middleware để xử lý request tiếp theo
    {
        $user = Auth::guard('api')->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Bạn không có quyền truy cập chức năng này!'], 403);
        }
        return $next($request);
    }
}