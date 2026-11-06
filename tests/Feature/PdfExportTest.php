<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportVerification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_pelapor_downloads_only_own_submitted_reports_as_pdf(): void
    {
        $owner = $this->person('pelapor_daerah');
        $other = $this->person('pelapor_daerah');
        $own = $this->report($owner, 'LP-20261101-1001', 'TERKIRIM');
        $this->report($owner, 'LP-20261101-1002', 'DRAF');
        $this->report($other, 'LP-20261101-1003', 'TERKIRIM');

        $response = $this->actingAs($owner)->get('/ekspor?format=pdf');
        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('.pdf', $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertTrue(str_contains($response->getContent(), mb_convert_encoding($own->nomor_tiket, 'UTF-16BE')), 'Own ticket absent from PDF content');
        $this->assertFalse(str_contains($response->getContent(), mb_convert_encoding('LP-20261101-1002', 'UTF-16BE')), 'Draft leaked into PDF');
        $this->assertFalse(str_contains($response->getContent(), mb_convert_encoding('LP-20261101-1003', 'UTF-16BE')), 'Another owner ticket leaked into PDF');
    }

    public function test_verifikator_date_filter_limits_pdf_rows(): void
    {
        $verifier = $this->person('verifikator_pusat');
        $owner = $this->person('pelapor_daerah');
        $inside = $this->report($owner, 'LP-20261101-1004', 'TERKIRIM');
        $inside->update(['dikirim_pada' => '2026-11-01 10:00:00']);
        $outside = $this->report($owner, 'LP-20261101-1005', 'TERKIRIM');
        $outside->update(['dikirim_pada' => '2026-10-01 10:00:00']);

        $response = $this->actingAs($verifier)->get('/ekspor?format=pdf&dari=2026-11-01&sampai=2026-11-30');
        $response->assertOk();
        $this->assertTrue(str_contains($response->getContent(), mb_convert_encoding($inside->nomor_tiket, 'UTF-16BE')), 'Included ticket absent from PDF content');
        $this->assertFalse(str_contains($response->getContent(), mb_convert_encoding($outside->nomor_tiket, 'UTF-16BE')), 'Out-of-period ticket leaked into PDF');
    }

    public function test_monitoring_pdf_export_preserves_region_and_verification_filters(): void
    {
        $executive = $this->person('pimpinan_kementan');
        $owner = $this->person('pelapor_daerah');
        $included = $this->report($owner, 'LP-20261101-3001', 'TERKIRIM');
        $included->update(['provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang']);
        $excluded = $this->report($owner, 'LP-20261101-3002', 'TERKIRIM');
        $excluded->update(['provinsi' => 'Jawa Tengah', 'kabupaten' => 'Semarang']);
        $this->verification($included, $executive, 'SESUAI');
        $this->verification($excluded, $executive, 'TIDAK_SESUAI');

        $response = $this->actingAs($executive)->get('/ekspor?format=pdf&provinsi=Jawa%20Barat&kabupaten=Subang&hasil=SESUAI');
        $response->assertOk();
        $this->assertTrue(str_contains($response->getContent(), mb_convert_encoding($included->nomor_tiket, 'UTF-16BE')));
        $this->assertFalse(str_contains($response->getContent(), mb_convert_encoding($excluded->nomor_tiket, 'UTF-16BE')));
    }

    public function test_pimpinan_monitoring_links_to_pdf_export(): void
    {
        $this->actingAs($this->person('pimpinan_kementan'))
            ->get('/monitoring')
            ->assertOk()
            ->assertSee('format=pdf', false);
    }

    public function test_super_admin_cannot_export_pdf(): void
    {
        $this->actingAs($this->person('super_admin'))->get('/ekspor?format=pdf')->assertForbidden();
    }

    private function person(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => $code]);
        return User::factory()->create(['role_id' => $role->id]);
    }

    private function report(User $owner, string $ticket, string $status): Report
    {
        return Report::create([
            'user_id' => $owner->id,
            'nomor_tiket' => $ticket,
            'status' => $status,
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Kerusakan saluran.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => $status === 'TERKIRIM' ? '2026-11-01 10:00:00' : null,
        ]);
    }

    private function verification(Report $report, User $verifier, string $result): void
    {
        ReportVerification::create([
            'report_id' => $report->id,
            'verifier_id' => $verifier->id,
            'hasil' => $result,
            'catatan' => 'Catatan pemeriksa.',
            'diperiksa_pada' => now(),
        ]);
    }
}
