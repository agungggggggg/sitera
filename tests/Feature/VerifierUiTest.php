<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class VerifierUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_verifier_can_see_all_submitted_reports(): void
    {
        $verifier = $this->userWithRole('verifikator_pusat');
        $reporterOne = $this->userWithRole('pelapor_daerah');
        $reporterTwo = User::factory()->create(['role_id' => Role::where('code', 'pelapor_daerah')->value('id')]);

        $this->submittedReport($reporterOne, 'LP-20261101-0001', 'Subang');
        $this->submittedReport($reporterTwo, 'LP-20261101-0002', 'Indramayu');
        $this->draftReport($reporterOne, 'LP-20261101-0003');

        $this->actingAs($verifier)
            ->get('/verifikasi')
            ->assertOk()
            ->assertSee('Antrean pemeriksaan')
            ->assertSee('LP-20261101-0001')
            ->assertSee('LP-20261101-0002')
            ->assertDontSee('LP-20261101-0003')
            ->assertSee('/sitera/verifikator-review.css')
            ->assertSee('Riwayat Pemeriksaan');
    }

    public function test_non_verifier_cannot_open_verifier_queue(): void
    {
        $reporter = $this->userWithRole('pelapor_daerah');

        $this->actingAs($reporter)->get('/verifikasi')->assertForbidden();
    }

    private function submittedReport(User $user, string $ticket, string $kabupaten): Report
    {
        return Report::create([
            'user_id' => $user->id,
            'nomor_tiket' => $ticket,
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => $kabupaten,
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Kerusakan untuk pengujian antrean.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);
    }

    private function draftReport(User $user, string $ticket): Report
    {
        return Report::create([
            'user_id' => $user->id,
            'nomor_tiket' => $ticket,
            'status' => 'DRAF',
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Karawang',
            'jenis_kerusakan' => 'Bendung',
            'deskripsi' => 'Draf tidak boleh masuk antrean.',
            'sumber_isian' => 'MANUAL',
        ]);
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
