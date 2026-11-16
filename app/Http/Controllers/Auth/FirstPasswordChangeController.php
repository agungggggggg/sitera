<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FirstPasswordChangeController extends Controller
{
    public function edit(Request $request): View
    {
        abort_unless($request->user()->must_change_password, 404);

        return view('auth.first-password-change', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->must_change_password, 404);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:50'],
            'email_confirmation' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'max:255'],
        ]);

        if (mb_strtolower(trim($validated['email_confirmation'])) !== mb_strtolower((string) $user->email)) {
            throw ValidationException::withMessages([
                'email_confirmation' => 'Email yang diketik ulang tidak cocok dengan email akun ini.',
            ]);
        }

        if (Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages(['password' => 'Password baru harus berbeda dari password awal.']);
        }

        DB::transaction(function () use ($user, $validated): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            abort_unless($lockedUser->must_change_password, 404);
            if (Hash::check($validated['password'], $lockedUser->password)) {
                throw ValidationException::withMessages(['password' => 'Password baru harus berbeda dari password awal.']);
            }
            $lockedUser->forceFill([
                'name' => $validated['name'],
                'contact' => $validated['contact'] ?? null,
                'password' => Hash::make($validated['password']),
                'must_change_password' => false,
                'is_active' => true,
            ])->save();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Akun berhasil diverifikasi dan diaktifkan. Silakan masuk menggunakan email dan password baru Anda.');
    }
}
