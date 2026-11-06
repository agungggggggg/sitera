<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_own_draft_detail(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'DRAF', 'LP-20261101-0001');

        $this->actingAs($owner)
            ->get('/laporan/'.$report->id)
            ->assertOk()
            ->assertSee($report->nomor_tiket)
            ->assertSee('Detail laporan');
    }

    public function test_verifier_can_view_submitted_detail(): void
    {
        $verifier = $this->userWithRole('verifikator_pusat');
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'TERKIRIM', 'LP-20261101-0002');

        $this->actingAs($verifier)
            ->get('/laporan/'.$report->id)
            ->assertOk()
            ->assertSee($report->nomor_tiket)
            ->assertSee('Detail laporan');
    }

    public function test_verifier_cannot_view_draft_detail(): void
    {
        $verifier = $this->userWithRole('verifikator_pusat');
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'DRAF', 'LP-20261101-0003');

        $this->actingAs($verifier)->get('/laporan/'.$report->id)->assertNotFound();
    }

    public function test_other_reporter_cannot_view_another_reporters_detail(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $other = User::factory()->create(['role_id' => Role::where('code', 'pelapor_daerah')->value('id')]);
        $report = $this->reportFor($owner, 'DRAF', 'LP-20261101-0004');

        $this->actingAs($other)->get('/laporan/'.$report->id)->assertNotFound();
    }

    private function reportFor(User $user, string $status, string $ticket): Report
    {
        return Report::create([
            'user_id' => $user->id,
            'nomor_tiket' => $ticket,
            'status' => $status,
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan',
            'desa' => 'Mulyasari',
            'nama_saluran' => 'Saluran Sekunder Ciasem',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Tanggul saluran mengalami kerusakan.',
            'panjang_terdampak_m' => 12.50,
            'luas_terdampak_ha' => 8.25,
            'koordinat' => '-6.287912, 107.942150',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => $status === 'TERKIRIM' ? now() : null,
        ]);
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
