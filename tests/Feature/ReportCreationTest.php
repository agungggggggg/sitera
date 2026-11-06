<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pelapor_can_create_a_manual_draft_report(): void
    {
        $user = $this->userWithRole('pelapor_daerah');

        $response = $this->actingAs($user)->post('/laporan', [
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan',
            'desa' => 'Mulyasari',
            'nama_saluran' => 'Saluran Sekunder Ciasem',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Tanggul saluran jebol sepanjang dua belas meter.',
            'panjang_terdampak_m' => '12.50',
            'luas_terdampak_ha' => '8.25',
            'koordinat' => '-6.287912, 107.942150',
            'catatan' => 'Perlu penanganan sementara.',
        ]);

        $response->assertRedirect('/laporan');
        $this->assertDatabaseCount('reports', 1);
        $this->assertDatabaseHas('reports', [
            'user_id' => $user->id,
            'status' => 'DRAF',
            'sumber_isian' => 'MANUAL',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
        ]);

        $this->assertMatchesRegularExpression(
            '/^LP-'.Carbon::parse('2026-10-30')->format('Ymd').'\-\d{4}$/',
            (string) \DB::table('reports')->value('nomor_tiket'),
        );
    }

    public function test_only_pelapor_can_create_a_report(): void
    {
        $user = $this->userWithRole('verifikator_pusat');

        $this->actingAs($user)->post('/laporan', [
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Kerusakan saluran.',
        ])->assertForbidden();

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_future_date_and_missing_required_fields_are_rejected(): void
    {
        $user = $this->userWithRole('pelapor_daerah');

        $response = $this->actingAs($user)->from('/laporan/buat')->post('/laporan', [
            'tanggal_kejadian' => now()->addDay()->toDateString(),
            'provinsi' => '',
            'kabupaten' => '',
            'jenis_kerusakan' => '',
            'deskripsi' => '',
        ]);

        $response->assertSessionHasErrors([
            'tanggal_kejadian',
            'provinsi',
            'kabupaten',
            'jenis_kerusakan',
            'deskripsi',
        ]);
        $this->assertDatabaseCount('reports', 0);
    }

    private function userWithRole(string $code): User
    {
        $role = Role::create(['code' => $code, 'name' => str_replace('_', ' ', $code)]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
