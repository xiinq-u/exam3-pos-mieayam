<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login untuk masuk ke aplikasi.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Mengecek email dan password.
     * Jika benar, user diarahkan ke dashboard.
     * Jika salah, user dikembalikan ke halaman login dengan pesan error.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
    }

    /**
     * Mengeluarkan user dari aplikasi.
     * Session dihapus supaya setelah logout user harus login lagi.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
