<?php

namespace Tests\Feature;

use App\Models\CpclRecord;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class AdminWorkspaceFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_filter_input_is_validated_instead_of_casting_arrays_or_ignoring_invalid_status(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->getJson('/admin-only?q[]=bad')->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->actingAs($admin)->getJson('/admin-only?status=invalid')->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->actingAs($admin)->getJson('/admin-only?role=invalid')->assertUnprocessable()->assertJsonValidationErrors('role');
    }

    public function test_cpcl_search_includes_visible_location_fields_and_combines_with_region_filters(): void
    {
        $admin = $this->admin();
        $first = CpclRecord::create(['nama_di' => 'DI Satu', 'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang', 'kecamatan' => 'Unik Pamanukan', 'desa' => 'Desa Satu', 'kel_tani' => 'Kelompok Satu']);
        $second = CpclRecord::create(['nama_di' => 'DI Dua', 'provinsi' => 'Jawa Timur', 'kabupaten' => 'Surabaya', 'kecamatan' => 'Unik Pamanukan', 'desa' => 'Desa Dua', 'kel_tani' => 'Kelompok Dua']);
        $this->actingAs($admin)->get('/admin-only?view=cpcl&q=Unik&provinsi=Jawa%20Barat&kabupaten=Subang')
            ->assertOk()->assertViewHas('cpclRecords', fn ($rows) => $rows->pluck('id')->all() === [$first->id])
            ->assertSee('name="provinsi"', false)->assertSee('name="kabupaten"', false);
        $this->actingAs($admin)->get('/admin-only?view=cpcl&q=Unik')
            ->assertOk()->assertViewHas('cpclRecords', fn ($rows) => $rows->total() === 2);
    }

    public function test_account_pagination_retains_search_role_and_status(): void
    {
        $admin = $this->admin();
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor']);
        User::factory()->count(23)->create(['name' => 'Pagination Target', 'role_id' => $role->id]);
        $this->actingAs($admin)->get('/admin-only?view=accounts&q=Pagination&role=pelapor_daerah&status=active')
            ->assertOk()->assertViewHas('users', fn ($rows) => $rows instanceof LengthAwarePaginator && $rows->total() === 23 && str_contains($rows->url(2), 'q=Pagination'));
    }

    public function test_dashboard_scope_rejects_array_input_for_admin_and_verifier(): void
    {
        $this->actingAs($this->admin())->getJson('/dashboard?cakupan[]=bad')->assertUnprocessable()->assertJsonValidationErrors('cakupan');
        $role = Role::firstOrCreate(['code' => 'verifikator_pusat'], ['name' => 'Verifikator']);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]))->getJson('/dashboard?cakupan[]=bad')
            ->assertUnprocessable()->assertJsonValidationErrors('cakupan');
    }

    private function admin(): User
    {
        $role = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
