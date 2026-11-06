<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'pelapor_daerah', 'name' => 'Pelapor Daerah'],
            ['code' => 'verifikator_pusat', 'name' => 'Verifikator Pusat'],
            ['code' => 'pimpinan_kementan', 'name' => 'Pimpinan Kementan'],
            ['code' => 'super_admin', 'name' => 'Super Admin'],
        ] as $role) {
            Role::query()->updateOrCreate(['code' => $role['code']], $role);
        }
    }
}
