<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verifier_can_mark_submitted_report_as_compliant(): void
    {
        $verifier = $this->userWithRole('verifikator_pusat');
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'LP-20261101-0001');

        $this->actingAs($verifier)
            ->post('/laporan/'.$report->id.'/verifikasi', [
                'hasil' => 'SESUAI',
                'catatan' => 'Dokumen dan uraian dapat diterima.',
            ])
            ->assertRedirect('/verifikasi/'.$report->id)
            ->assertSessionHas('status');

        $this->assertDatabaseHas('report_verifications', [
            'report_id' => $report->id,
            'verifier_id' => $verifier->id,
            'hasil' => 'SESUAI',
            'catatan' => 'Dokumen dan uraian dapat diterima.',
        ]);
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'TERKIRIM']);
    }

    public function test_note_is_required_when_report_is_not_compliant(): void
    {
        $verifier = $this->userWithRole('verifikator_pusat');
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'LP-20261101-0002');

        $this->actingAs($verifier)
            ->from('/laporan/'.$report->id)
            ->post('/laporan/'.$report->id.'/verifikasi', [
                'hasil' => 'TIDAK_SESUAI',
                'catatan' => '',
            ])
            ->assertSessionHasErrors('catatan');

        $this->assertDatabaseCount('report_verifications', 0);
    }

    public function test_non_verifier_cannot_verify_and_verified_report_cannot_be_verified_again(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $other = User::factory()->create(['role_id' => Role::where('code', 'pelapor_daerah')->value('id')]);
        $report = $this->reportFor($owner, 'LP-20261101-0003');

        $this->actingAs($other)
            ->post('/laporan/'.$report->id.'/verifikasi', ['hasil' => 'SESUAI'])
            ->assertForbidden();

        $verifier = $this->userWithRole('verifikator_pusat');
        $this->actingAs($verifier)->post('/laporan/'.$report->id.'/verifikasi', [
            'hasil' => 'SESUAI',
            'catatan' => 'Sudah diperiksa.',
        ])->assertRedirect('/verifikasi/'.$report->id);

        $this->actingAs($verifier)
            ->post('/laporan/'.$report->id.'/verifikasi', [
                'hasil' => 'TIDAK_SESUAI',
                'catatan' => 'Percobaan kedua.',
            ])
            ->assertStatus(422);
    }

    private function reportFor(User $user, string $ticket): Report
    {
        return Report::create([
            'user_id' => $user->id,
            'nomor_tiket' => $ticket,
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan untuk pengujian verifikasi.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
