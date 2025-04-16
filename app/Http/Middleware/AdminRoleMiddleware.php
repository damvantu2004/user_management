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
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('api')->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Bạn không có quyền truy cập chức năng này!'], 403);
        }
        return $next($request);
    }
}