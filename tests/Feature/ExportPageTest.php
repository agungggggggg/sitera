<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_pelapor_sees_export_form_with_supported_formats_and_date_filters(): void
    {
        $user = $this->userWithRole('pelapor_daerah');

        $this->actingAs($user)->get('/ekspor')
            ->assertOk()
            ->assertSee('Ekspor rekap')
            ->assertSee('name="dari"', false)
            ->assertSee('name="sampai"', false)
            ->assertSee('CSV')
            ->assertSee('Excel (.xlsx)')
            ->assertSee('PDF');
    }

    public function test_super_admin_cannot_access_export_page(): void
    {
        $this->actingAs($this->userWithRole('super_admin'))
            ->get('/ekspor')
            ->assertForbidden();
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
