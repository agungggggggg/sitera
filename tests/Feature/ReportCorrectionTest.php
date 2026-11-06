<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportVerification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportCorrectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_draft_correction_from_not_compliant_report(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $original = $this->submittedReport($owner);
        ReportVerification::create([
            'report_id' => $original->id,
            'verifier_id' => $this->userWithRole('verifikator_pusat')->id,
            'hasil' => 'TIDAK_SESUAI',
            'catatan' => 'Deskripsi dan lokasi perlu diperjelas.',
            'diperiksa_pada' => now(),
        ]);

        $response = $this->actingAs($owner)->post('/laporan/'.$original->id.'/koreksi');

        $response->assertRedirectContains('/koreksi/edit');
        $correction = Report::where('koreksi_dari', $original->id)->firstOrFail();
        $this->assertSame('DRAF', $correction->status);
        $this->assertSame($owner->id, $correction->user_id);
        $this->assertSame('Koreksi dari '.$original->nomor_tiket, $correction->catatan);
    }

    public function test_owner_cannot_create_correction_from_compliant_report(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $original = $this->submittedReport($owner);
        ReportVerification::create([
            'report_id' => $original->id,
            'verifier_id' => $this->userWithRole('verifikator_pusat')->id,
            'hasil' => 'SESUAI',
            'catatan' => 'Sesuai.',
            'diperiksa_pada' => now(),
        ]);

        $this->actingAs($owner)->post('/laporan/'.$original->id.'/koreksi')->assertStatus(422);
        $this->assertDatabaseCount('reports', 1);
    }

    public function test_other_user_cannot_create_correction(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $other = User::factory()->create(['role_id' => Role::where('code', 'pelapor_daerah')->value('id')]);
        $original = $this->submittedReport($owner);
        ReportVerification::create([
            'report_id' => $original->id,
            'verifier_id' => $this->userWithRole('verifikator_pusat')->id,
            'hasil' => 'TIDAK_SESUAI',
            'catatan' => 'Perlu diperbaiki.',
            'diperiksa_pada' => now(),
        ]);

        $this->actingAs($other)->post('/laporan/'.$original->id.'/koreksi')->assertNotFound();
        $this->assertDatabaseCount('reports', 1);
    }

    private function submittedReport(User $user): Report
    {
        return Report::create([
            'user_id' => $user->id,
            'nomor_tiket' => 'LP-20261101-0001-'.$user->id,
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan awal.',
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
