<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $role = Role::create(['code' => 'pelapor_daerah', 'name' => 'Pelapor Daerah']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'pelapor@example.test',
            'password' => Hash::make('secret-password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'pelapor@example.test']);

        $response = $this->from('/login')->post('/login', [
            'email' => 'pelapor@example.test',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_role_middleware_denies_wrong_role(): void
    {
        $role = Role::create(['code' => 'pelapor_daerah', 'name' => 'Pelapor Daerah']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get('/admin-only');

        $response->assertForbidden();
    }

    public function test_role_middleware_allows_matching_role(): void
    {
        $role = Role::create(['code' => 'super_admin', 'name' => 'Super Admin']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get('/admin-only');

        $response->assertOk()->assertSee('Super Admin');
    }
}
