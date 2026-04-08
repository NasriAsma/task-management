<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        $hasAnyRole = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $hasAnyRole = true;
                break;
            }
        }

        if (!$hasAnyRole) {
            return response()->json(['message' => 'Forbidden - Role required: ' . implode(' or ', $roles)], 403);
        }

        return $next($request);
    }
}
