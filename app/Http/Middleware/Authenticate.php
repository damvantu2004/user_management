<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string //
    {
        return null;
    }
    protected function unauthenticated($request, array $guards) //
    {
        abort(response()->json([
            'status' => 'error',
            'message' => 'Bạn chưa đăng nhập hoặc phiên làm việc đã hết hạn'
        ], 401));
    }
}
