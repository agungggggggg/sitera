<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);
        $identifier = trim((string) ($validated['login'] ?? $validated['email'] ?? ''));
        $errorField = $request->filled('login') ? 'login' : 'email';
        if ($identifier === '') {
            return back()->withErrors([$errorField => 'Masukkan username atau email.']);
        }

        $throttleKey = Str::transliterate(Str::lower($identifier).'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors([$errorField => 'Terlalu banyak percobaan login. Coba lagi beberapa saat.'])
                ->onlyInput($errorField);
        }

        $normalizedIdentifier = mb_strtolower($identifier);
        $user = User::query()
            ->where('username', $normalizedIdentifier)
            ->orWhereRaw('LOWER(email) = ?', [$normalizedIdentifier])
            ->first();

        $mayAuthenticate = $user
            && Hash::check($validated['password'], $user->password)
            && ($user->is_active || $user->must_change_password);

        if (! $mayAuthenticate) {
            RateLimiter::hit($throttleKey, 60);
            app(AuditLogger::class)->log(null, 'auth.login', 'failure');

            return back()->withErrors([$errorField => 'Username/email atau password tidak dapat digunakan.'])
                ->onlyInput($errorField);
        }

        RateLimiter::clear($throttleKey);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        app(AuditLogger::class)->log($user, 'auth.login', 'success', 'user', $user->id);

        return $user->must_change_password
            ? redirect()->route('password.first-login')
            : redirect()->intended('/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        app(AuditLogger::class)->log($request->user(), 'auth.logout', 'success', 'user', $request->user()->id);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
