<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportVerification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorrectionActionUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_owner_sees_correction_action_for_not_compliant_report(): void
    {
        [$owner, $report] = $this->reportWithVerification('TIDAK_SESUAI');

        $this->actingAs($owner)
            ->get('/laporan/'.$report->id)
            ->assertOk()
            ->assertSee('Perlu koreksi')
            ->assertSee('Buat draf koreksi');
    }

    public function test_report_owner_does_not_see_correction_action_for_compliant_report(): void
    {
        [$owner, $report] = $this->reportWithVerification('SESUAI');

        $this->actingAs($owner)
            ->get('/laporan/'.$report->id)
            ->assertOk()
            ->assertDontSee('Buat draf koreksi');
    }

    private function reportWithVerification(string $result): array
    {
        $reporterRole = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        $verifierRole = Role::firstOrCreate(['code' => 'verifikator_pusat'], ['name' => 'Verifikator Pusat']);
        $owner = User::factory()->create(['role_id' => $reporterRole->id]);
        $verifier = User::factory()->create(['role_id' => $verifierRole->id]);
        $report = Report::create([
            'user_id' => $owner->id,
            'nomor_tiket' => 'LP-20261101-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Uji tampilan tindakan koreksi.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);
        ReportVerification::create([
            'report_id' => $report->id,
            'verifier_id' => $verifier->id,
            'hasil' => $result,
            'catatan' => $result === 'TIDAK_SESUAI' ? 'Lokasi dan uraian perlu diperjelas.' : 'Data dapat diterima.',
            'diperiksa_pada' => now(),
        ]);

        return [$owner, $report];
    }
}
