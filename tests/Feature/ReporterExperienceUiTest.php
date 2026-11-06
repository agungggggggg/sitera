<?php

namespace Tests\Feature;

use App\Models\CpclRecord;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporterExperienceUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporter_dashboard_uses_review_visual_assets_and_operational_package_summary(): void
    {
        $reporter = $this->reporter();
        $cpcl = $this->cpcl('Subang', 'P3A Tirta');
        Report::create([
            'user_id' => $reporter->id, 'cpcl_id' => $cpcl->id, 'nomor_tiket' => 'LP-20261103-0001',
            'status' => 'DRAF', 'versi' => 1, 'tanggal_kejadian' => '2026-11-03', 'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang', 'kecamatan' => 'Pamanukan', 'desa' => 'Mulyasari', 'nama_saluran' => 'DI Pamanukan',
            'jenis_kerusakan' => 'Paket CPCL', 'deskripsi' => 'Paket dokumen P3A Tirta', 'sumber_isian' => 'MANUAL',
        ]);

        $this->actingAs($reporter)->get('/dashboard')
            ->assertOk()
            ->assertSee('/sitera/theme.css')
            ->assertSee('/sitera/pelapor-review.css')
            ->assertSee('Ringkasan laporan')
            ->assertSee('Buat Laporan')
            ->assertSee('Ringkasan kelengkapan')
            ->assertSee('P3A Tirta')
            ->assertDontSee('Selamat datang,');
    }

    public function test_reporter_can_search_and_filter_only_own_reports_in_demo_style_report_list(): void
    {
        $reporter = $this->reporter();
        $firstCpcl = $this->cpcl('Subang', 'P3A Tirta');
        $secondCpcl = $this->cpcl('Subang', 'Poktan Harapan');
        $otherReporter = $this->reporter(['email' => 'other@example.test']);
        $otherCpcl = $this->cpcl('Subang', 'P3A Sumber');

        $this->report($reporter, $firstCpcl, 'LP-20261103-0001', 'DRAF');
        $this->report($reporter, $secondCpcl, 'LP-20261103-0002', 'TERKIRIM');
        $this->report($otherReporter, $otherCpcl, 'LP-20261103-0003', 'DRAF');

        $this->actingAs($reporter)->get(route('reports.index', ['q' => 'P3A Tirta', 'status' => 'DRAF']))
            ->assertOk()
            ->assertSee('LP-20261103-0001')
            ->assertDontSee('LP-20261103-0002')
            ->assertDontSee('LP-20261103-0003')
            ->assertSee('Filter');
    }

    public function test_report_list_filter_options_remain_distinct_sorted_and_owner_scoped(): void
    {
        $reporter = $this->reporter();
        $firstCpcl = CpclRecord::create([
            'nama_di' => 'DI Zeta', 'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan', 'desa' => 'Mulyasari', 'kel_tani' => 'Kelompok Zeta',
        ]);
        $secondCpcl = CpclRecord::create([
            'nama_di' => 'DI Alpha', 'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan', 'desa' => 'Sukamandi', 'kel_tani' => 'Kelompok Alpha',
        ]);
        $otherReporter = $this->reporter(['email' => 'options-other@example.test']);
        $otherCpcl = CpclRecord::create([
            'nama_di' => 'DI Private', 'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan', 'desa' => 'Legonkulon', 'kel_tani' => 'Kelompok Private',
        ]);
        $blankCpcl = CpclRecord::create([
            'nama_di' => '', 'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan', 'desa' => 'Blank', 'kel_tani' => '',
        ]);
        $this->report($reporter, $firstCpcl, 'LP-20261103-0021', 'DRAF');
        $this->report($reporter, $secondCpcl, 'LP-20261103-0022', 'TERKIRIM');
        $this->report($reporter, $firstCpcl, 'LP-20261103-0025', 'DRAF');
        $this->report($reporter, $blankCpcl, 'LP-20261103-0026', 'DRAF');
        Report::create([
            'user_id' => $reporter->id, 'nomor_tiket' => 'LP-20261103-0027', 'status' => 'DRAF',
            'tanggal_kejadian' => '2026-11-03', 'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder', 'deskripsi' => 'Laporan manual tanpa CPCL.', 'sumber_isian' => 'MANUAL',
        ]);
        $this->report($otherReporter, $otherCpcl, 'LP-20261103-0024', 'DRAF');

        $response = $this->actingAs($reporter)->get(route('reports.index'));
        $response->assertOk()
            ->assertViewHas('groups', fn ($groups) => $groups->all() === ['Kelompok Alpha', 'Kelompok Zeta'])
            ->assertViewHas('irrigations', fn ($irrigations) => $irrigations->all() === ['DI Alpha', 'DI Zeta'])
            ->assertViewHas('reports', fn ($reports) => $reports->total() === 5 && $reports->count() === 5);

        $this->actingAs($reporter)->get(route('reports.index', ['q' => 'LP-20261103-0022', 'sort' => 'date-asc', 'page' => 1]))
            ->assertOk()
            ->assertViewHas('filters', fn ($filters) => $filters['q'] === 'LP-20261103-0022' && $filters['sort'] === 'date-asc')
            ->assertViewHas('reports', fn ($reports) => $reports->total() === 1 && $reports->first()->nomor_tiket === 'LP-20261103-0022')
            ->assertViewHas('groups', fn ($groups) => $groups->all() === ['Kelompok Alpha', 'Kelompok Zeta'])
            ->assertViewHas('irrigations', fn ($irrigations) => $irrigations->all() === ['DI Alpha', 'DI Zeta']);
    }

    public function test_all_reports_screen_is_a_read_only_summary_without_detail_links(): void
    {
        $reporter = $this->reporter();
        $cpcl = $this->cpcl('Subang', 'P3A Tirta');
        $report = $this->report($reporter, $cpcl, 'LP-20261103-0011', 'TERKIRIM');

        $this->actingAs($reporter)->get(route('reports.all'))
            ->assertOk()
            ->assertSee('Ringkasan lintas kabupaten')
            ->assertSee($report->nomor_tiket)
            ->assertSee('P3A Tirta')
            ->assertDontSee('href="'.route('reports.show', $report).'"', false)
            ->assertDontSee('Dokumen laporan');
    }

    public function test_cpcl_package_screen_uses_review_layout_and_has_draft_and_submit_actions(): void
    {
        $reporter = $this->reporter();
        $cpcl = $this->cpcl('Subang', 'P3A Tirta');

        $this->actingAs($reporter)->get(route('reports.package.create', $cpcl))
            ->assertOk()
            ->assertSee('/sitera/pelapor-review.css')
            ->assertSee('Dokumen pelaporan')
            ->assertSee('BAST')
            ->assertSee('LPJ')
            ->assertSee('Dokumentasi')
            ->assertSee('name="_action" value="draft"', false)
            ->assertSee('name="_action" value="submit"', false);
    }

    private function reporter(array $attributes = []): User
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);

        return User::factory()->create($attributes + [
            'role_id' => $role->id,
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
        ]);
    }

    private function cpcl(string $regency, string $group): CpclRecord
    {
        return CpclRecord::create([
            'nama_di' => 'DI Pamanukan', 'provinsi' => 'Jawa Barat', 'kabupaten' => $regency,
            'kecamatan' => 'Pamanukan', 'desa' => 'Mulyasari', 'kel_tani' => $group,
        ]);
    }

    private function report(User $user, CpclRecord $cpcl, string $ticket, string $status): Report
    {
        return Report::create([
            'user_id' => $user->id, 'cpcl_id' => $cpcl->id, 'nomor_tiket' => $ticket, 'status' => $status,
            'versi' => 1, 'tanggal_kejadian' => '2026-11-03', 'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan', 'desa' => 'Mulyasari', 'nama_saluran' => 'DI Pamanukan',
            'jenis_kerusakan' => 'Paket CPCL', 'deskripsi' => 'Paket dokumen '.$cpcl->kel_tani, 'sumber_isian' => 'MANUAL',
        ]);
    }
}
