<?php

namespace Tests\Feature;

use App\Models\CpclRecord;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportRegionSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_entry_shows_only_operational_cpcl_from_assigned_region(): void
    {
        $user = $this->userWithRole('pelapor_daerah');
        $user->forceFill(['provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang'])->save();
        $cpcl = CpclRecord::create([
            'nama_di' => 'DI Subang', 'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan', 'desa' => 'Mulyasari', 'kel_tani' => 'P3A Tirta',
        ]);

        $this->actingAs($user)->get('/laporan/buat')
            ->assertOk()
            ->assertSee('Pilih CPCL')
            ->assertSee('DI Subang')
            ->assertSee('P3A Tirta')
            ->assertSee(route('reports.package.create', $cpcl), false);
    }

    public function test_report_rejects_regency_that_does_not_belong_to_selected_province(): void
    {
        $user = $this->userWithRole('pelapor_daerah');

        $response = $this->actingAs($user)->post('/laporan', [
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Semarang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Saluran rusak.',
        ]);

        $response->assertSessionHasErrors('kabupaten');
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_report_accepts_regency_in_selected_province(): void
    {
        $user = $this->userWithRole('pelapor_daerah');

        $this->actingAs($user)->post('/laporan', [
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Saluran rusak.',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reports', ['provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang']);
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => 'Pelapor Daerah']);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
