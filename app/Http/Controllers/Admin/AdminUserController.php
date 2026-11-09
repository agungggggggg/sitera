<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\RegionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function verifyPic(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role?->code === 'pelapor_daerah' && filled($user->contact), 404);

        if ($user->pic_verified_at) {
            return redirect('/admin-only?view=accounts')->with('status', 'PIC akun tersebut sudah diverifikasi.');
        }

        $user->forceFill([
            'pic_verified_at' => now(),
            'pic_verified_by' => $request->user()->id,
        ])->save();

        app(AuditLogger::class)->log($request->user(), 'user.pic_verified', 'success', 'user', $user->id);

        return redirect('/admin-only?view=accounts')->with('status', 'PIC akun '.$user->username.' berhasil diverifikasi. Status aktivasi password tidak berubah.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            throw ValidationException::withMessages(['user' => 'Akun Super Admin yang sedang digunakan tidak dapat dihapus.']);
        }

        $user->delete();

        app(AuditLogger::class)->log($request->user(), 'user.deleted', 'success', 'user', $user->id);

        return redirect('/admin-only?view=accounts')->with('status', 'Akun '.$user->username.' dihapus dari daftar aktif. Riwayat laporan tetap dipertahankan.');
    }

    public function setActive(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);
        if ($user->is($request->user())) {
            throw ValidationException::withMessages(['user' => 'Akun Super Admin yang sedang digunakan tidak dapat dinonaktifkan.']);
        }
        if ($user->must_change_password && $validated['is_active']) {
            throw ValidationException::withMessages(['user' => 'Pengguna harus menyelesaikan penggantian password pertama sebelum akun diaktifkan.']);
        }

        $user->forceFill(['is_active' => $validated['is_active']])->save();

        app(AuditLogger::class)->log($request->user(), 'user.status_changed', 'success', 'user', $user->id, ['is_active' => (bool) $user->is_active]);

        return redirect('/admin-only?view=accounts')->with('status', $validated['is_active'] ? 'Akun berhasil diaktifkan.' : 'Akun berhasil dinonaktifkan.');
    }

    public function store(Request $request, RegionCatalog $regions): RedirectResponse
    {
        abort_unless(is_string(config('sitera.default_password')) && config('sitera.default_password') !== '', 503, 'Password default belum dikonfigurasi.');
        $request->merge(['username' => mb_strtolower(trim((string) $request->input('username', '')))]);

        $province = (string) $request->input('provinsi', '');
        $regencies = array_column($regions->regenciesFor($province), 'value');
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:80', 'regex:/^[a-z0-9._-]+$/', 'unique:users,username'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact' => ['nullable', 'string', 'max:50'],
            'role' => ['required', Rule::in(['pelapor_daerah', 'verifikator_pusat', 'pimpinan_kementan'])],
            'provinsi' => ['nullable', 'required_if:role,pelapor_daerah', 'string', Rule::in($regions->provinceNames())],
            'kabupaten' => ['nullable', 'required_if:role,pelapor_daerah', 'string', Rule::in($regencies)],
        ]);

        if ($validated['role'] === 'pelapor_daerah' && User::query()->where('role_id', Role::query()->where('code', 'pelapor_daerah')->value('id'))
            ->where('provinsi', $validated['provinsi'])->where('kabupaten', $validated['kabupaten'])->exists()) {
            throw ValidationException::withMessages(['kabupaten' => 'Wilayah ini sudah memiliki akun Pelapor.']);
        }

        $roleNames = [
            'pelapor_daerah' => 'Pelapor Daerah',
            'verifikator_pusat' => 'Verifikator Pusat',
            'pimpinan_kementan' => 'Pimpinan Kementan',
        ];
        $role = Role::firstOrCreate(['code' => $validated['role']], ['name' => $roleNames[$validated['role']]]);

        $createdUser = User::create([
            'username' => strtolower($validated['username']),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'contact' => $validated['contact'] ?? null,
            'role_id' => $role->id,
            'provinsi' => $validated['provinsi'] ?? null,
            'kabupaten' => $validated['kabupaten'] ?? null,
            'password' => Hash::make(config('sitera.default_password')),
            'is_active' => false,
            'must_change_password' => true,
        ]);

        app(AuditLogger::class)->log($request->user(), 'user.created', 'success', 'user', $createdUser->id);

        return redirect('/admin-only?view=accounts')->with('status', 'Akun '.$validated['username'].' dibuat. Status Menunggu aktivasi; pengguna wajib mengganti password awal saat login pertama.');
    }
}
