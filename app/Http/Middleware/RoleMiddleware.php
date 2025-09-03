<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            $token = $request->cookie('token'); 

            if ($token) {
                $user = JWTAuth::setToken($token)->authenticate();
            } else {
                $user = JWTAuth::parseToken()->authenticate(); // fallback to header
            }

            if (!in_array($user->role, $roles)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
