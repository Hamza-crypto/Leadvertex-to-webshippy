<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowedIPMiddleware
{
    public $allowed_ips = ['124.109.46.230', '119.160'];

    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $ipFragments = explode('.', $ip);

        // Check if the IP address matches the allowed IPs
        foreach ($this->allowed_ips as $allowedIp) {
            $allowedIpFragments = explode('.', $allowedIp);

            // Compare the first two fragments
            if ($ipFragments[0] == $allowedIpFragments[0] && $ipFragments[1] == $allowedIpFragments[1]) {
                return $next($request);
            }
        }

        // IP address is not allowed
        return response()->json(['message' => 'Unauthorized'], 401);
    }
}

