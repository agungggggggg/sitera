<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccountStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_deactivate_a_user_and_the_user_cannot_log_in(): void
    {
        config(['sitera.default_password' => 'Account-test-default-32!']);
        $admin = $this->userWithRole('super_admin');
        $reporter = $this->userWithRole('pelapor_daerah', ['email' => 'subang@example.test', 'password' => Hash::make('initial-secret')]);

        $this->actingAs($admin)->patch(route('admin.users.status', $reporter), ['is_active' => '0'])
            ->assertRedirect('/admin-only?view=accounts');
        $this->assertDatabaseHas('users', ['id' => $reporter->id, 'is_active' => 0]);
        $this->post('/logout');

        $this->post('/login', ['email' => $reporter->email, 'password' => 'initial-secret'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_super_admin_cannot_deactivate_own_account(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)->patch(route('admin.users.status', $admin), ['is_active' => '0'])
            ->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_active' => 1]);
    }

    private function userWithRole(string $code, array $attributes = []): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => $code]);

        return User::factory()->create($attributes + ['role_id' => $role->id]);
    }
}
