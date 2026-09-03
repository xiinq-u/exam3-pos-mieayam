<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = $request->user();

        // Jika tidak ada role yang dispesifikasi, izinkan akses
        if (empty($roles)) {
            return $next($request);
        }

        // Pecah roles dari format "role1,role2" menjadi array
        $allowedRoles = [];
        foreach ($roles as $roleStr) {
            $allowedRoles = array_merge($allowedRoles, explode(',', $roleStr));
        }

        if (! $user->hasRole($allowedRoles)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return $next($request);
    }
}
