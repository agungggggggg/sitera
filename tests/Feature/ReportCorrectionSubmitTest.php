<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportVerification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportCorrectionSubmitTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_submit_a_valid_correction_draft(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $original = $this->submittedReport($owner, 'LP-20261101-0001');
        $this->markNotCompliant($original, $owner);
        $correction = $this->correctionFor($owner, $original, 'LP-20261101-0002');

        $this->actingAs($owner)
            ->post('/laporan/'.$correction->id.'/koreksi/kirim')
            ->assertRedirect('/laporan')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('reports', [
            'id' => $correction->id,
            'status' => 'TERKIRIM',
        ]);
        $this->assertNotNull($correction->fresh()->dikirim_pada);
        $this->assertDatabaseHas('reports', [
            'id' => $original->id,
            'status' => 'TERKIRIM',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $owner->id,
            'action' => 'report.submitted',
            'result' => 'success',
            'subject_type' => 'report',
            'subject_id' => (string) $correction->id,
        ]);
    }

    public function test_invalid_correction_cannot_be_submitted(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $original = $this->submittedReport($owner, 'LP-20261101-0003');
        $this->markNotCompliant($original, $owner);
        $correction = $this->correctionFor($owner, $original, 'LP-20261101-0004');
        $correction->update(['deskripsi' => '']);

        $this->actingAs($owner)
            ->from('/laporan/'.$correction->id.'/koreksi/edit')
            ->post('/laporan/'.$correction->id.'/koreksi/kirim')
            ->assertSessionHasErrors('deskripsi');

        $this->assertDatabaseHas('reports', ['id' => $correction->id, 'status' => 'DRAF']);
    }

    public function test_other_user_cannot_submit_correction_and_submitted_correction_cannot_repeat(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $other = User::factory()->create(['role_id' => Role::where('code', 'pelapor_daerah')->value('id')]);
        $original = $this->submittedReport($owner, 'LP-20261101-0005');
        $this->markNotCompliant($original, $owner);
        $correction = $this->correctionFor($owner, $original, 'LP-20261101-0006');

        $this->actingAs($other)
            ->post('/laporan/'.$correction->id.'/koreksi/kirim')
            ->assertNotFound();

        $this->actingAs($owner)
            ->post('/laporan/'.$correction->id.'/koreksi/kirim')
            ->assertRedirect('/laporan');

        $this->actingAs($owner)
            ->post('/laporan/'.$correction->id.'/koreksi/kirim')
            ->assertStatus(422);

        $this->assertDatabaseCount('audit_logs', 1);
        $verifier = User::whereHas('role', fn ($q) => $q->where('code', 'verifikator_pusat'))->first();
        $this->assertSame(1, $verifier->notifications()->count());
    }

    private function submittedReport(User $user, string $ticket): Report
    {
        return Report::create([
            'user_id' => $user->id,
            'nomor_tiket' => $ticket,
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

    private function correctionFor(User $owner, Report $original, string $ticket): Report
    {
        $report = Report::create([
            'user_id' => $owner->id,
            'koreksi_dari' => $original->id,
            'nomor_tiket' => $ticket,
            'status' => 'DRAF',
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Koreksi yang sudah lengkap.',
            'sumber_isian' => 'MANUAL',
            'catatan' => 'Koreksi dari '.$original->nomor_tiket,
        ]);
        $report->attachments()->create([
            'path' => 'tests/correction.jpg',
            'original_name' => 'correction.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 100,
        ]);

        return $report;
    }

    private function markNotCompliant(Report $report, User $owner): void
    {
        ReportVerification::create([
            'report_id' => $report->id,
            'verifier_id' => $this->userWithRole('verifikator_pusat')->id,
            'hasil' => 'TIDAK_SESUAI',
            'catatan' => 'Perlu koreksi.',
            'diperiksa_pada' => now(),
        ]);
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);
        return User::factory()->create(['role_id' => $role->id]);
    }
}
