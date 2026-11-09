<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use RuntimeException;

class AdminPasswordResetController extends Controller
{
    public function forgot(): View
    {
        return view('auth.forgot-password');
    }

    public function reset(User $user): RedirectResponse
    {
        $defaultPassword = config('sitera.default_password');

        if (! is_string($defaultPassword) || $defaultPassword === '') {
            throw new RuntimeException('SITERA_DEFAULT_PASSWORD belum dikonfigurasi.');
        }

        $user->forceFill([
            'password' => Hash::make($defaultPassword),
            'is_active' => false,
            'must_change_password' => true,
        ])->save();

        app(AuditLogger::class)->log(request()->user(), 'user.password_reset', 'success', 'user', $user->id);

        return redirect('/admin-only?view=accounts')->with('status', 'Password awal akun diperbarui. Pengguna harus mengganti password saat login berikutnya. Nilai password tidak ditampilkan.');
    }
}
