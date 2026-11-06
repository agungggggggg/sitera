<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalEntryPointTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_operational_login_instead_of_static_prototype(): void
    {
        $this->get('/')
            ->assertRedirect('/login');
    }

    public function test_authenticated_reporter_root_redirects_to_laravel_dashboard(): void
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user)->followingRedirects()->get('/')
            ->assertOk()
            ->assertSee('Buat Laporan')
            ->assertSee('href="'.route('reports.create').'"', false);
    }
}
