<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class CompleteSearchFilterDemoProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $isIsolatedTest = app()->environment('testing')
            && config('database.default') === 'sqlite'
            && config('database.connections.sqlite.database') === ':memory:';
        if (! $isIsolatedTest && ! config('demo.approved_target', false)) {
            throw new RuntimeException('Demo profile completion is disabled outside the approved local database target.');
        }
        $existing = User::query()->whereIn('username', [
            'filter-demo-barat', 'filter-demo-timur', 'filter-demo-verifikator',
        ])->orderBy('username')->get()->keyBy('username');
        if ($existing->count() !== 3 || $existing->contains(fn (User $user) => $user->role?->code !== match ($user->username) {
            'filter-demo-barat', 'filter-demo-timur' => 'pelapor_daerah',
            'filter-demo-verifikator' => 'verifikator_pusat',
        })) {
            throw new RuntimeException('Expected the three existing FILTER-DEMO profiles; nothing changed.');
        }
        if (User::withTrashed()->whereIn('username', ['filter-demo-admin', 'filter-demo-pimpinan'])->exists()) {
            throw new RuntimeException('Additional FILTER-DEMO profiles already exist; nothing changed.');
        }

        $password = bin2hex(random_bytes(12));
        $hash = Hash::make($password);
        $profiles = DB::transaction(function () use ($existing, $hash): array {
            User::query()->whereIn('id', $existing->pluck('id'))->update(['password' => $hash]);
            $superAdminRole = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
            $executiveRole = Role::firstOrCreate(['code' => 'pimpinan_kementan'], ['name' => 'Pimpinan Kementan']);
            $admin = $this->create('filter-demo-admin', 'FILTER-DEMO Super Admin', $superAdminRole->id, $hash);
            $executive = $this->create('filter-demo-pimpinan', 'FILTER-DEMO Pimpinan', $executiveRole->id, $hash);
            $actor = User::query()->whereHas('role', fn ($query) => $query->where('code', 'super_admin'))
                ->where('is_active', true)->where('must_change_password', false)->orderBy('id')->firstOrFail();
            $audit = app(AuditLogger::class);
            $audit->log($actor, 'user.created', 'success', 'user', $admin->id);
            $audit->log($actor, 'user.created', 'success', 'user', $executive->id);

            return compact('admin', 'executive');
        });

        $this->command?->info('Added Super Admin and Pimpinan FILTER-DEMO profiles; aligned all five demo accounts to a newly generated temporary password. Existing real accounts unchanged.');
        $this->command?->line('Pelapor Barat: filter-demo-barat / '.$password);
        $this->command?->line('Pelapor Timur: filter-demo-timur / '.$password);
        $this->command?->line('Verifikator: filter-demo-verifikator / '.$password);
        $this->command?->line('Super Admin: '.$profiles['admin']->username.' / '.$password);
        $this->command?->line('Pimpinan: '.$profiles['executive']->username.' / '.$password);
    }

    private function create(string $username, string $name, int $roleId, string $passwordHash): User
    {
        return User::query()->create([
            'username' => $username,
            'name' => $name,
            'email' => $username.'@example.test',
            'password' => $passwordHash,
            'role_id' => $roleId,
            'is_active' => true,
            'must_change_password' => false,
        ]);
    }
}
