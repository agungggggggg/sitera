<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifierUiFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_verifier_dashboard_uses_review_ui_and_real_database_metrics(): void
    {
        $verifier = $this->user('verifikator_pusat');
        $report = $this->report($this->user('pelapor_daerah'), 'LP-20261103-7001');

        $this->actingAs($verifier)->get('/dashboard')
            ->assertOk()
            ->assertSee('/sitera/verifikator-review.css')
            ->assertSee('Ringkasan pemeriksaan')
            ->assertSee('1')
            ->assertSee($report->nomor_tiket)
            ->assertSee('Rekap Status CPCL');
    }

    public function test_queue_filters_submitted_unreviewed_reports_and_opens_review_detail(): void
    {
        $verifier = $this->user('verifikator_pusat');
        $owner = $this->user('pelapor_daerah');
        $report = $this->report($owner, 'LP-20261103-7002');
        $other = $this->report($owner, 'LP-20261103-7003', 'Laporan lain');

        $this->actingAs($verifier)->get('/verifikasi?q=7002')
            ->assertOk()
            ->assertSee($report->nomor_tiket)
            ->assertDontSee($other->nomor_tiket)
            ->assertSee('Periksa');

        $this->actingAs($verifier)->get('/verifikasi/'.$report->id)
            ->assertOk()
            ->assertSee('Hasil pemeriksaan gabungan')
            ->assertSee('Tidak sesuai')
            ->assertSee('name="catatan"', false);
    }

    public function test_verifier_history_is_limited_to_reviews_made_by_current_user(): void
    {
        $verifier = $this->user('verifikator_pusat');
        $otherVerifier = $this->user('verifikator_pusat');
        $owner = $this->user('pelapor_daerah');
        $mine = $this->report($owner, 'LP-20261103-7004');
        $theirs = $this->report($owner, 'LP-20261103-7005');
        $mine->verification()->create(['verifier_id' => $verifier->id, 'hasil' => 'SESUAI', 'catatan' => null, 'diperiksa_pada' => now()]);
        $theirs->verification()->create(['verifier_id' => $otherVerifier->id, 'hasil' => 'TIDAK_SESUAI', 'catatan' => 'Perlu koreksi.', 'diperiksa_pada' => now()]);

        $this->actingAs($verifier)->get('/verifikasi/riwayat')
            ->assertOk()
            ->assertSee($mine->nomor_tiket)
            ->assertDontSee($theirs->nomor_tiket);
    }

    public function test_non_verifier_cannot_access_queue_or_review_detail(): void
    {
        $reporter = $this->user('pelapor_daerah');
        $report = $this->report($reporter, 'LP-20261103-7006');

        $this->actingAs($reporter)->get('/verifikasi')->assertForbidden();
        $this->actingAs($reporter)->get('/verifikasi/'.$report->id)->assertForbidden();
    }

    public function test_history_combines_region_result_and_review_date_without_leaking_other_verifiers(): void
    {
        $verifier = $this->user('verifikator_pusat');
        $owner = $this->user('pelapor_daerah');
        $mine = $this->report($owner, 'LP-20261103-7111');
        $other = $this->report($owner, 'LP-20261103-7112');
        $other->update(['provinsi' => 'Jawa Timur', 'kabupaten' => 'Surabaya']);
        foreach ([$mine, $other] as $report) {
            $report->verification()->create(['verifier_id' => $verifier->id, 'hasil' => 'SESUAI', 'diperiksa_pada' => '2026-11-03 10:00:00']);
        }
        $this->actingAs($verifier)->get('/verifikasi/riwayat?provinsi=Jawa%20Barat&result=Sesuai&date_from=2026-11-03&date_to=2026-11-03')
            ->assertOk()->assertViewHas('reviews', fn ($rows) => $rows->pluck('report_id')->all() === [$mine->id]);
        $this->actingAs($verifier)->getJson('/verifikasi/riwayat?sort=group-asc')
            ->assertUnprocessable()->assertJsonValidationErrors('sort');
        $this->actingAs($verifier)->getJson('/verifikasi?date_from=2026-11-10&date_to=2026-11-01')
            ->assertUnprocessable()->assertJsonValidationErrors('date_to');
    }

    private function user(string $roleCode): User
    {
        $role = Role::firstOrCreate(['code' => $roleCode], ['name' => str_replace('_', ' ', $roleCode)]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function report(User $owner, string $ticket, string $description = 'Dokumen paket CPCL menunggu pemeriksaan.'): Report
    {
        return Report::create([
            'user_id' => $owner->id,
            'nomor_tiket' => $ticket,
            'status' => 'TERKIRIM',
            'versi' => 1,
            'tanggal_kejadian' => now()->toDateString(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan',
            'desa' => 'Mulyasari',
            'nama_saluran' => 'DI Ciasem',
            'jenis_kerusakan' => 'Paket CPCL',
            'deskripsi' => $description,
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);
    }
}
