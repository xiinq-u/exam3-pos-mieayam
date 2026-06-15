<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Penjaga halaman admin.
     * Jika belum login, user diarahkan ke login.
     * Jika sudah login tapi bukan admin, akses ditolak.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (! isset($user->is_admin) || ! $user->is_admin) {
            abort(403);
        }

        return $next($request);
    }
}
