<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportVerification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExecutiveUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pimpinan_can_see_national_summary(): void
    {
        $executive = $this->userWithRole('pimpinan_kementan');
        $owner = $this->userWithRole('pelapor_daerah');
        $report = Report::create([
            'user_id' => $owner->id,
            'nomor_tiket' => 'LP-20261101-0001',
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Kerusakan nasional.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);
        ReportVerification::create([
            'report_id' => $report->id,
            'verifier_id' => $this->userWithRole('verifikator_pusat')->id,
            'hasil' => 'SESUAI',
            'catatan' => 'Sesuai.',
            'diperiksa_pada' => now(),
        ]);

        $this->actingAs($executive)
            ->get('/monitoring')
            ->assertOk()
            ->assertSee('Rekap paket laporan')
            ->assertSee('Jawa Barat')
            ->assertSee('LP-20261101-0001')
            ->assertSee('Detail')
            ->assertSee('/sitera/pimpinan-review.css')
            ->assertSee('Ringkasan');
    }

    public function test_non_executive_cannot_open_national_summary(): void
    {
        $user = $this->userWithRole('pelapor_daerah');

        $this->actingAs($user)->get('/monitoring')->assertForbidden();
    }

    public function test_pimpinan_can_filter_reports_by_date_region_and_verification_result(): void
    {
        $executive = $this->userWithRole('pimpinan_kementan');
        $owner = $this->userWithRole('pelapor_daerah');
        $included = $this->report($owner, 'LP-20261101-2001', 'Jawa Barat', 'Subang', '2026-11-02 10:00:00');
        $excludedRegion = $this->report($owner, 'LP-20261101-2002', 'Jawa Tengah', 'Semarang', '2026-11-02 10:00:00');
        $excludedPeriod = $this->report($owner, 'LP-20261101-2003', 'Jawa Barat', 'Subang', '2026-10-02 10:00:00');
        $this->verification($included, 'SESUAI');
        $this->verification($excludedRegion, 'SESUAI');
        $this->verification($excludedPeriod, 'TIDAK_SESUAI');

        $this->actingAs($executive)
            ->get('/monitoring?dari=2026-11-01&sampai=2026-11-30&provinsi=Jawa%20Barat&kabupaten=Subang&hasil=SESUAI')
            ->assertOk()
            ->assertSee($included->nomor_tiket)
            ->assertDontSee($excludedRegion->nomor_tiket)
            ->assertDontSee($excludedPeriod->nomor_tiket)
            ->assertSee('name="provinsi"', false)
            ->assertSee('name="status"', false)
            ->assertSee('value="SESUAI" selected', false);
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function report(User $owner, string $ticket, string $province, string $regency, string $submittedAt): Report
    {
        return Report::create([
            'user_id' => $owner->id,
            'nomor_tiket' => $ticket,
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => $province,
            'kabupaten' => $regency,
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan untuk uji filter.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => Carbon::parse($submittedAt),
        ]);
    }

    private function verification(Report $report, string $result): void
    {
        ReportVerification::create([
            'report_id' => $report->id,
            'verifier_id' => $this->userWithRole('verifikator_pusat')->id,
            'hasil' => $result,
            'catatan' => 'Hasil pemeriksaan.',
            'diperiksa_pada' => now(),
        ]);
    }
}
