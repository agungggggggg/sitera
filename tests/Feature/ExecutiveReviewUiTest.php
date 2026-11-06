<?php

namespace Tests\Feature;

use App\Models\CpclRecord;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutiveReviewUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pimpinan_dashboard_uses_demo_layout_and_database_backed_draft_and_sent_metrics(): void
    {
        $executive = $this->user('pimpinan_kementan');
        $reporter = $this->user('pelapor_daerah');
        $cpcl = $this->cpcl();
        $this->report($reporter, $cpcl, 'LP-20261103-8001', 'DRAF');
        $sent = $this->report($reporter, $cpcl, 'LP-20261103-8002', 'TERKIRIM');

        $this->actingAs($executive)->get('/dashboard')
            ->assertOk()
            ->assertSee('/sitera/pimpinan-review.css')
            ->assertSee('Ringkasan rekapitulasi')
            ->assertSee('2 paket')
            ->assertSee('Draf')
            ->assertSee($sent->nomor_tiket)
            ->assertSee('Rekap Status CPCL');
    }

    public function test_pimpinan_rekap_searches_and_filters_drafts_and_sent_reports_read_only(): void
    {
        $executive = $this->user('pimpinan_kementan');
        $reporter = $this->user('pelapor_daerah');
        $cpcl = $this->cpcl();
        $included = $this->report($reporter, $cpcl, 'LP-20261103-8003', 'DRAF');
        $excluded = $this->report($reporter, $cpcl, 'LP-20261103-8004', 'TERKIRIM', 'Kelompok lain');

        $this->actingAs($executive)->get('/monitoring?q=8003&status=DRAF')
            ->assertOk()
            ->assertSee('/sitera/pimpinan-review.css')
            ->assertSee($included->nomor_tiket)
            ->assertDontSee($excluded->nomor_tiket)
            ->assertSee('name="status"', false);
    }

    public function test_pimpinan_detail_is_read_only_and_does_not_offer_private_file_downloads(): void
    {
        $executive = $this->user('pimpinan_kementan');
        $reporter = $this->user('pelapor_daerah');
        $report = $this->report($reporter, $this->cpcl(), 'LP-20261103-8005', 'TERKIRIM');

        $this->actingAs($executive)->get('/monitoring/'.$report->id)
            ->assertOk()
            ->assertSee('Detail paket laporan')
            ->assertSee($report->nomor_tiket)
            ->assertSee('baca saja')
            ->assertDontSee(route('reports.attachments.download', 1));

        $this->actingAs($executive)->get('/laporan/'.$report->id)->assertNotFound();
    }

    public function test_non_executive_cannot_access_leadership_routes(): void
    {
        $reporter = $this->user('pelapor_daerah');
        $report = $this->report($reporter, $this->cpcl(), 'LP-20261103-8010', 'TERKIRIM');

        $this->actingAs($reporter)->get('/monitoring')->assertForbidden();
        $this->actingAs($reporter)->get('/monitoring/'.$report->id)->assertForbidden();
    }

    public function test_export_uses_the_same_search_and_cpcl_filters_as_the_recap(): void
    {
        $executive = $this->user('pimpinan_kementan');
        $owner = $this->user('pelapor_daerah');
        $cpcl = $this->cpcl();
        $included = $this->report($owner, $cpcl, 'LP-20261103-8101', 'TERKIRIM');
        $excluded = $this->report($owner, $cpcl, 'LP-20261103-8102', 'TERKIRIM');
        $query = http_build_query(['q' => '8101', 'kelompok' => $cpcl->kel_tani, 'desa' => $cpcl->desa]);

        $this->actingAs($executive)->get('/monitoring?'.$query)
            ->assertOk()->assertViewHas('exportUrl', fn ($url) => str_contains($url, 'q=8101') && str_contains($url, 'desa='));
        $response = $this->actingAs($executive)->get('/ekspor?format=csv&'.$query)->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString($included->nomor_tiket, $content);
        $this->assertStringNotContainsString($excluded->nomor_tiket, $content);
    }

    public function test_filters_reject_arrays_and_reversed_dates_and_preserve_empty_selections(): void
    {
        $executive = $this->user('pimpinan_kementan');
        $this->actingAs($executive)->getJson('/monitoring?date_from=2026-11-20&date_to=2026-11-01')
            ->assertUnprocessable()->assertJsonValidationErrors('date_to');
        $this->actingAs($executive)->getJson('/monitoring?q[]=bad')->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->actingAs($executive)->get('/monitoring?sort=&status=&provinsi=')->assertOk();
        $this->actingAs($executive)->get('/monitoring?date_to=2026-11-30')->assertOk();
        $this->actingAs($executive)->get('/ekspor?format=csv&sampai=2026-11-30')->assertOk();
        $this->actingAs($executive)->getJson('/ekspor?format=csv&sort=invalid')->assertUnprocessable()->assertJsonValidationErrors('sort');
    }

    public function test_geographic_dropdown_options_follow_the_selected_parent_region(): void
    {
        $executive = $this->user('pimpinan_kementan');
        $this->cpcl();
        CpclRecord::create(['nama_di' => 'DI Timur', 'provinsi' => 'Jawa Timur', 'kabupaten' => 'Surabaya', 'kecamatan' => 'Kecamatan Timur', 'desa' => 'Desa Timur', 'kel_tani' => 'Kelompok Timur']);
        $this->actingAs($executive)->get('/monitoring?provinsi=Jawa%20Barat')
            ->assertOk()->assertViewHas('regencies', fn ($options) => $options->contains('Subang') && ! $options->contains('Surabaya'))
            ->assertViewHas('villages', fn ($options) => ! $options->contains('Desa Timur'));
    }

    private function cpcl(): CpclRecord
    {
        return CpclRecord::create([
            'no' => 'EX-01', 'nama_di' => 'DI Karang', 'kewenangan' => 'Kabupaten', 'keterangan' => 'CPCL test',
            'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang', 'kecamatan' => 'Pamanukan', 'desa' => 'Mulyasari',
            'kel_tani' => 'P3A Karang', 'nama_ketua' => 'Ketua Test', 'realisasi' => '75%', 'luas_ha' => 12.5,
        ]);
    }

    private function report(User $owner, CpclRecord $cpcl, string $ticket, string $status, string $group = 'P3A Karang'): Report
    {
        return Report::create([
            'user_id' => $owner->id, 'cpcl_id' => $cpcl->id, 'nomor_tiket' => $ticket, 'status' => $status, 'versi' => 1,
            'tanggal_kejadian' => now()->toDateString(), 'provinsi' => $cpcl->provinsi, 'kabupaten' => $cpcl->kabupaten,
            'kecamatan' => $cpcl->kecamatan, 'desa' => $cpcl->desa, 'nama_saluran' => $cpcl->nama_di,
            'jenis_kerusakan' => 'Paket CPCL', 'deskripsi' => $group, 'sumber_isian' => 'MANUAL',
            'dikirim_pada' => $status === 'TERKIRIM' ? now() : null,
        ]);
    }

    private function user(string $roleCode): User
    {
        $role = Role::firstOrCreate(['code' => $roleCode], ['name' => str_replace('_', ' ', $roleCode)]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
