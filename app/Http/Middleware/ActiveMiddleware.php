<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ admin.'
            ], 403);
        }

        return $next($request);
    }
}