<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if ($user->must_change_password) {
            if ($request->routeIs('password.first-login', 'password.first-login.update', 'logout')) {
                return $next($request);
            }

            return redirect()->route('password.first-login');
        }

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['login' => 'Akun ini sedang dinonaktifkan. Hubungi Super Admin.']);
        }

        return $next($request);
    }
}
