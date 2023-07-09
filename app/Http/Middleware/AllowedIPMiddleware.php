<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowedIPMiddleware
{
    public $allowed_ips = ['124.109.46.230', '119.160.20.190'];

    public function handle(Request $request, Closure $next)
    {
        if (!in_array($request->ip(), $this->allowed_ips)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
