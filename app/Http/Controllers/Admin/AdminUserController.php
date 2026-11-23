<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\RegionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    /**
     * Placeholder identity used until the account owner completes first-login activation.
     */
    private const PENDING_NAME = 'Pengguna Baru';

    public function verifyPic(Request $request, User $user): RedirectResponse
    {
        abort_unless(
            $user->role?->code === 'pelapor_daerah'
            && (filled($user->contact) || ! $user->must_change_password),
            404
        );

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

        $displayLabel = $user->username ?: $user->email;

        DB::transaction(function () use ($user): void {
            $this->releaseTrashedIdentity($user);
            $user->delete();
        });

        app(AuditLogger::class)->log($request->user(), 'user.deleted', 'success', 'user', $user->id);

        return redirect('/admin-only?view=accounts')->with('status', 'Akun '.$displayLabel.' dihapus dari daftar aktif. Riwayat laporan tetap dipertahankan.');
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
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email', '')))]);

        $province = (string) $request->input('provinsi', '');
        $regencies = array_column($regions->regenciesFor($province), 'value');
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->withoutTrashed()],
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

        $createdUser = DB::transaction(function () use ($validated, $role): User {
            User::onlyTrashed()
                ->where('email', $validated['email'])
                ->get()
                ->each(fn (User $trashed): mixed => $this->releaseTrashedIdentity($trashed));

            return User::create([
                'username' => null,
                'name' => self::PENDING_NAME,
                'email' => $validated['email'],
                'contact' => null,
                'role_id' => $role->id,
                'provinsi' => $validated['provinsi'] ?? null,
                'kabupaten' => $validated['kabupaten'] ?? null,
                'password' => Hash::make(config('sitera.default_password')),
                'is_active' => false,
                'must_change_password' => true,
            ]);
        });

        app(AuditLogger::class)->log($request->user(), 'user.created', 'success', 'user', $createdUser->id);

        return redirect('/admin-only?view=accounts')->with('status', 'Akun '.$validated['email'].' dibuat. Status Menunggu aktivasi; pengguna melengkapi nama PIC dan membuat password baru saat login pertama.');
    }

    /**
     * Free unique email and username columns on a soft-deleted account so the address can be re-issued.
     */
    private function releaseTrashedIdentity(User $user): void
    {
        $suffix = '.deleted.'.$user->id.'.'.now()->timestamp;
        $maxPrefixLength = max(1, 255 - mb_strlen($suffix));
        $updates = ['is_active' => false];

        if ($user->email !== null && preg_match('/\.deleted\.\d+\.\d+$/', $user->email) !== 1) {
            $updates['email'] = mb_substr($user->email, 0, $maxPrefixLength, 'UTF-8').$suffix;
        }

        if ($user->username !== null && preg_match('/\.deleted\.\d+\.\d+$/', $user->username) !== 1) {
            $updates['username'] = mb_substr($user->username, 0, $maxPrefixLength, 'UTF-8').$suffix;
        }

        $user->forceFill($updates)->save();
    }
}
