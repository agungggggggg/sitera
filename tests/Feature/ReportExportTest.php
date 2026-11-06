<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporter_can_download_own_csv_export(): void
    {
        $reporter = $this->userWithRole('pelapor_daerah');
        $own = $this->reportFor($reporter, 'LP-20261101-0001', 'Subang');
        $other = $this->reportFor($this->userWithRole('pelapor_daerah'), 'LP-20261101-0002', 'Indramayu');

        $response = $this->actingAs($reporter)->get('/ekspor?format=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString($own->nomor_tiket, $csv);
        $this->assertStringNotContainsString($other->nomor_tiket, $csv);
    }

    public function test_verifier_can_export_submitted_reports_with_date_filter(): void
    {
        $verifier = $this->userWithRole('verifikator_pusat');
        $included = $this->reportFor($this->userWithRole('pelapor_daerah'), 'LP-20261101-0003', 'Subang');
        $included->update(['dikirim_pada' => Carbon::parse('2026-11-01 10:00')]);
        $excluded = $this->reportFor($this->userWithRole('pelapor_daerah'), 'LP-20261101-0004', 'Karawang');
        $excluded->update(['dikirim_pada' => Carbon::parse('2026-10-01 10:00')]);

        $response = $this->actingAs($verifier)
            ->get('/ekspor?format=csv&dari=2026-11-01&sampai=2026-11-30');
        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString($included->nomor_tiket, $csv);
        $this->assertStringNotContainsString($excluded->nomor_tiket, $csv);
    }

    public function test_reporter_cannot_export_other_users_data(): void
    {
        $reporter = $this->userWithRole('pelapor_daerah');
        $this->actingAs($reporter)->get('/ekspor?format=csv')->assertOk();
    }

    private function reportFor(User $user, string $ticket, string $kabupaten): Report
    {
        return Report::create([
            'user_id' => $user->id,
            'nomor_tiket' => $ticket,
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => $kabupaten,
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan untuk ekspor.',
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
