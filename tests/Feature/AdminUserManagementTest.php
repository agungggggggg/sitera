<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_a_reporter_account_with_configured_default_password(): void
    {
        config(['sitera.default_password' => 'Local-test-default-22!']);
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)->post('/admin/users', [
            'username' => 'pelapor-subang',
            'name' => 'Petugas Daerah',
            'email' => 'pelapor@example.test',
            'role' => 'pelapor_daerah',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
        ])->assertRedirect('/admin-only?view=accounts');

        $user = User::where('username', 'pelapor-subang')->firstOrFail();
        $this->assertSame('pelapor_daerah', $user->role->code);
        $this->assertTrue(Hash::check('Local-test-default-22!', $user->password));
        $this->assertTrue($user->must_change_password);
        $this->assertFalse($user->is_active);
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $admin->id, 'action' => 'user.created', 'subject_id' => (string) $user->id, 'result' => 'success']);
        $this->get('/admin-only?view=audit&action=user.created')->assertOk()->assertSee('user.created');
    }

    public function test_super_admin_cannot_create_an_account_without_email(): void
    {
        config(['sitera.default_password' => 'Local-test-default-22!']);
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)->post('/admin/users', [
            'username' => 'tanpa-email',
            'name' => 'PIC Tanpa Email',
            'role' => 'pelapor_daerah',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['username' => 'tanpa-email']);
    }

    public function test_non_admin_cannot_create_user_accounts(): void
    {
        $user = $this->userWithRole('pelapor_daerah');

        $this->actingAs($user)->post('/admin/users', [
            'name' => 'User Baru',
            'email' => 'new@example.test',
            'role' => 'pelapor_daerah',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'new@example.test']);
    }

    public function test_super_admin_cannot_create_another_super_admin_through_user_form(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Admin Baru',
            'email' => 'another-admin@example.test',
            'role' => 'super_admin',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'another-admin@example.test']);
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
