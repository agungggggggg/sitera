<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\CompleteSearchFilterDemoProfilesSeeder;
use Database\Seeders\SearchFilterDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SearchFilterDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_only_explicitly_marked_search_filter_fixtures(): void
    {
        $adminRole = Role::create(['code' => 'super_admin', 'name' => 'Super Admin']);
        $existingAdmin = User::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        app(SearchFilterDemoSeeder::class)->run();
        app(CompleteSearchFilterDemoProfilesSeeder::class)->run();

        $this->assertDatabaseHas('users', ['id' => $existingAdmin->id]);
        $this->assertDatabaseCount('users', 6);
        $this->assertDatabaseCount('cpcl_records', 3);
        $this->assertDatabaseCount('reports', 5);
        $this->assertDatabaseCount('report_verifications', 2);
        $this->assertDatabaseCount('audit_logs', 6);
        foreach (['super_admin', 'pelapor_daerah', 'verifikator_pusat', 'pimpinan_kementan'] as $roleCode) {
            $this->assertDatabaseHas('roles', ['code' => $roleCode]);
        }
        $this->assertDatabaseHas('reports', ['nomor_tiket' => 'FILTER-DEMO-LP-0001', 'status' => 'DRAF']);
        $this->assertDatabaseHas('reports', ['nomor_tiket' => 'FILTER-DEMO-LP-0004', 'nama_saluran' => 'FILTER-DEMO Saluran fallback']);
    }

    public function test_seeder_refuses_duplicate_fixtures_without_changing_existing_rows(): void
    {
        Role::create(['code' => 'super_admin', 'name' => 'Super Admin']);
        User::factory()->create(['role_id' => Role::query()->where('code', 'super_admin')->value('id'), 'is_active' => true, 'must_change_password' => false]);

        app(SearchFilterDemoSeeder::class)->run();

        try {
            app(SearchFilterDemoSeeder::class)->run();
            $this->fail('Expected an idempotency guard failure.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('already exists', $exception->getMessage());
        }

        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('cpcl_records', 3);
        $this->assertDatabaseCount('reports', 5);
        $this->assertDatabaseCount('audit_logs', 4);
    }
}
