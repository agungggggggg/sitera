<?php

namespace App\Http\Controllers;

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
            'password' => ['required', 'string', 'min:8', 'confirmed', 'max:255'],
        ]);

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
                'password' => Hash::make($validated['password']),
                'must_change_password' => false,
                'is_active' => true,
            ])->save();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Password berhasil diubah dan akun telah diaktifkan. Silakan masuk menggunakan username dan password baru.');
    }
}
