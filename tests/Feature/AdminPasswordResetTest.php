<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_directs_user_to_super_admin(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Hubungi Super Admin');
    }

    public function test_super_admin_can_reset_user_to_configured_default_password(): void
    {
        config(['sitera.default_password' => 'configured-default']);
        $admin = $this->userWithRole('super_admin');
        $target = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($admin)
            ->post('/admin/users/'.$target->id.'/reset-password')
            ->assertRedirect('/admin-only?view=accounts')
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('configured-default', $target->fresh()->password));
    }

    public function test_console_recovery_resets_the_existing_super_admin_to_the_new_default_without_printing_it(): void
    {
        config(['sitera.default_password' => 'new-configured-default']);
        $admin = $this->userWithRole('super_admin');
        $admin->forceFill(['password' => Hash::make('previous-default')])->save();

        $this->post('/login', ['email' => $admin->email, 'password' => 'new-configured-default'])
            ->assertSessionHasErrors('email');

        $this->artisan('sitera:reset-super-admin-password', ['--force' => true])
            ->expectsOutputToContain('Password reset completed. Secret value was not displayed.')
            ->doesntExpectOutputToContain('new-configured-default')
            ->assertExitCode(0);

        $this->assertTrue(Hash::check('new-configured-default', $admin->fresh()->password));
        $this->post('/login', ['email' => $admin->email, 'password' => 'new-configured-default'])
            ->assertRedirect('/dashboard');
    }

    public function test_non_super_admin_cannot_reset_a_password(): void
    {
        $actor = $this->userWithRole('pelapor_daerah');
        $target = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($actor)
            ->post('/admin/users/'.$target->id.'/reset-password')
            ->assertForbidden();

        $this->assertTrue(Hash::check('old-password', $target->fresh()->password));
    }

    private function userWithRole(string $code): User
    {
        $role = Role::create(['code' => $code, 'name' => str_replace('_', ' ', $code)]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
