<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = auth('api')->user();
        if(! $user) return response()->json(['message'=>'unauthenticated'], 401);

        if(! in_array($user->role, $roles)) {
            return response()->json(['message'=>'forbidden'], 403);
        }

        return $next($request);
    }
}
