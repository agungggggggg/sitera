<?php

namespace Tests\Feature;

use App\Models\CpclRecord;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReporterCpclFilterValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_filters_reject_malformed_values_before_querying(): void
    {
        $this->actingAs($this->reporter());
        $invalid = [];
        foreach (['q', 'provinsi', 'kabupaten', 'kecamatan', 'desa', 'kelompok_tani', 'nama_di', 'status', 'date_from', 'date_to', 'sort', 'page'] as $field) {
            $invalid[] = [$field => ['unexpected']];
        }
        $invalid = array_merge($invalid, [
            ['q' => str_repeat('x', 256)], ['status' => 'unknown'], ['sort' => 'unsafe'],
            ['date_from' => '2026-02-30'], ['date_to' => 'tomorrow'],
            ['date_from' => '2026-11-04', 'date_to' => '2026-11-03'], ['page' => '0'],
        ]);
        foreach (['reports.index', 'reports.all'] as $route) {
            foreach ($invalid as $query) {
                $this->getJson(route($route, $query))->assertUnprocessable()->assertJsonValidationErrors(array_keys($query)[count($query) - 1]);
            }
        }
    }

    public function test_draft_filter_cannot_broaden_submitted_only_summary(): void
    {
        $user = $this->reporter();
        $this->report($user, ['status' => 'TERKIRIM']);
        $this->report($user, ['status' => 'DRAF']);
        $this->actingAs($user)->get(route('reports.all', ['status' => 'DRAF']))
            ->assertOk()->assertViewHas('reports', fn ($reports) => $reports->total() === 0);
    }

    public function test_report_search_matches_displayed_locations_without_leaking_other_owners(): void
    {
        $user = $this->reporter();
        $other = $this->reporter();
        $report = $this->report($user);
        $this->report($other);
        foreach (['Jawa Barat', 'Pamanukan', 'Mulyasari', 'Saluran lokal'] as $term) {
            $this->actingAs($user)->get(route('reports.index', ['q' => $term]))->assertOk()
                ->assertViewHas('reports', fn ($reports) => $reports->pluck('id')->all() === [$report->id]);
            $this->get(route('reports.all', ['q' => $term]))->assertOk()
                ->assertViewHas('reports', fn ($reports) => $reports->total() === 2);
        }
    }

    public function test_report_sort_ties_have_stable_pagination(): void
    {
        $this->freezeTime();
        $user = $this->reporter();
        $ids = [];
        for ($i = 0; $i < 22; $i++) {
            $ids[] = $this->report($user, ['updated_at' => '2026-11-03 12:00:00'])->id;
        }
        $ids = array_reverse($ids);
        $this->actingAs($user);
        foreach (['reports.index', 'reports.all'] as $route) {
            foreach (['date-desc', 'date-asc', 'group-asc', 'group-desc', 'di-asc', 'di-desc', 'kab-asc', 'kab-desc'] as $sort) {
                foreach ([1, 2] as $page) {
                    $expected = array_slice($ids, ($page - 1) * 20, 20);
                    $this->get(route($route, compact('sort', 'page')))->assertOk()
                        ->assertViewHas('reports', fn ($reports) => $reports->pluck('id')->all() === $expected);
                }
            }
        }
    }

    public function test_cpcl_filters_reject_malformed_search_district_detail_and_page(): void
    {
        $this->actingAs($this->reporter());
        foreach ([['q' => ['x']], ['kecamatan' => ['x']], ['detail' => ['x']], ['page' => ['x']],
            ['q' => str_repeat('x', 256)], ['kecamatan' => str_repeat('x', 256)],
            ['detail' => 'abc'], ['detail' => '0'], ['detail' => '1.5'], ['page' => '0']] as $query) {
            $this->getJson(route('reports.create', $query))->assertUnprocessable()->assertJsonValidationErrors(array_keys($query));
        }
    }

    public function test_cpcl_search_matches_displayed_region_without_crossing_region_or_active_scope(): void
    {
        $user = $this->reporter();
        $local = $this->cpcl();
        $outside = $this->cpcl(['kabupaten' => 'Indramayu']);
        $inactive = $this->cpcl(['aktif' => false, 'kel_tani' => 'Inactive']);
        $this->actingAs($user);
        foreach (['Jawa Barat', 'Subang', 'Pamanukan'] as $term) {
            $this->get(route('reports.create', ['q' => $term]))->assertOk()
                ->assertViewHas('records', fn ($records) => $records->pluck('id')->all() === [$local->id]);
        }
        foreach ([$outside->id, $inactive->id] as $detail) {
            $this->get(route('reports.create', compact('detail')))->assertOk()->assertViewHas('selectedCpcl', null);
        }
        $this->get(route('reports.create', ['detail' => $local->id, 'kecamatan' => 'missing']))->assertOk()
            ->assertViewHas('records', fn ($records) => $records->total() === 0)
            ->assertViewHas('selectedCpcl', fn ($selected) => $selected->id === $local->id);
    }

    public function test_cpcl_pagination_has_an_explicit_unique_tie_breaker(): void
    {
        $user = $this->reporter();
        for ($i = 22; $i >= 1; $i--) {
            $this->cpcl(['id' => $i, 'desa' => 'Desa '.$i]);
        }
        $response = $this->actingAs($user)->get(route('reports.create'));
        $response->assertOk()->assertViewHas('records', fn ($records) => $records->pluck('id')->all() === range(1, 20));
        $this->get(route('reports.create', ['page' => 2]))->assertOk()
            ->assertViewHas('records', fn ($records) => $records->pluck('id')->all() === [21, 22]);
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });
        $this->get(route('reports.create'))->assertOk();
        $this->assertTrue(collect($queries)->contains(fn ($sql) => str_contains($sql, 'order by "nama_di" asc, "kel_tani" asc, "id" asc')));
    }

    private function cpcl(array $attributes = []): CpclRecord
    {
        return CpclRecord::create($attributes + [
            'no' => '1', 'nama_di' => 'DI Pamanukan', 'kel_tani' => 'P3A Tirta',
            'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang', 'kecamatan' => 'Pamanukan', 'desa' => 'Mulyasari',
        ]);
    }

    private function report(User $user, array $attributes = []): Report
    {
        return Report::create($attributes + [
            'user_id' => $user->id, 'nomor_tiket' => 'FILTER-'.Report::count(), 'status' => 'TERKIRIM',
            'versi' => 1, 'tanggal_kejadian' => '2026-11-03', 'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang', 'kecamatan' => 'Pamanukan', 'desa' => 'Mulyasari',
            'nama_saluran' => 'Saluran lokal', 'jenis_kerusakan' => 'Paket CPCL',
            'deskripsi' => 'Paket dokumen', 'sumber_isian' => 'MANUAL', 'dikirim_pada' => '2026-11-03 12:00:00',
        ]);
    }

    private function reporter(): User
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);

        return User::factory()->create(['role_id' => $role->id, 'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang']);
    }
}
