<?php

namespace Tests\Feature;

use App\Models\CpclRecord;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CpclFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_add_cpcl_and_it_is_persisted(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)->post('/admin/cpcl', $this->cpclData())
            ->assertRedirect('/admin-only')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('cpcl_records', [
            'nama_di' => 'DI Contoh Barat',
            'kel_tani' => 'P3A Tirta Contoh',
            'kabupaten' => 'Subang',
        ]);
    }

    public function test_duplicate_cpcl_identity_is_rejected(): void
    {
        $admin = $this->userWithRole('super_admin');
        $this->actingAs($admin)->post('/admin/cpcl', $this->cpclData())->assertRedirect('/admin-only');

        $this->actingAs($admin)->post('/admin/cpcl', $this->cpclData())
            ->assertSessionHasErrors('kel_tani');

        $this->assertDatabaseCount('cpcl_records', 1);
    }

    public function test_report_can_reference_cpcl_record(): void
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $cpcl = CpclRecord::create($this->cpclData());
        $report = Report::create([
            'user_id' => $user->id,
            'cpcl_id' => $cpcl->id,
            'nomor_tiket' => 'LP-20261101-9881',
            'status' => 'DRAF',
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan berbasis CPCL.',
            'sumber_isian' => 'MANUAL',
        ]);

        $this->assertSame($cpcl->id, $report->cpclRecord->id);
    }

    private function cpclData(): array
    {
        return [
            'no' => '1',
            'nama_di' => 'DI Contoh Barat',
            'kewenangan' => 'Kabupaten/Kota',
            'keterangan' => 'Data sumber',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan',
            'desa' => 'Mulyasari',
            'kel_tani' => 'P3A Tirta Contoh',
            'nama_ketua' => 'Nama Ketua',
            'realisasi' => '1',
            'luas_ha' => '25.0',
            'x' => '107.1234',
            'y' => '-6.1234',
        ];
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => $code]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
