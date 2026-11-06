<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FirstLoginPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_reporter_must_change_default_password_then_relogin_before_account_is_activated(): void
    {
        config(['sitera.default_password' => 'Initial-default-42!']);
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)->post('/admin/users', [
            'username' => 'surabaya',
            'name' => 'Petugas Surabaya',
            'contact' => 'PIC Surabaya',
            'email' => 'surabaya@example.test',
            'role' => 'pelapor_daerah',
            'provinsi' => 'Jawa Timur',
            'kabupaten' => 'Surabaya',
        ])->assertRedirect('/admin-only?view=accounts');

        $reporter = User::query()->where('username', 'surabaya')->firstOrFail();
        $this->assertTrue(Hash::check('Initial-default-42!', $reporter->password));
        $this->assertTrue($reporter->must_change_password);
        $this->assertFalse($reporter->is_active);
        $this->get('/admin-only?view=accounts')
            ->assertOk()
            ->assertSee('surabaya')
            ->assertSee('Menunggu aktivasi');
        $this->post('/logout');

        $this->post('/login', ['login' => 'surabaya', 'password' => 'Initial-default-42!'])
            ->assertRedirect('/password/first-login');
        $this->get('/dashboard')->assertRedirect('/password/first-login');
        $this->get('/password/first-login')->assertOk()->assertSee('Ubah Password')->assertSee('surabaya');

        $this->post('/password/first-login', [
            'password' => 'Reporter-personal-94!',
            'password_confirmation' => 'Reporter-personal-94!',
        ])->assertRedirect('/login')->assertSessionHas('status');

        $reporter->refresh();
        $this->assertFalse($reporter->must_change_password);
        $this->assertTrue($reporter->is_active);
        $this->assertTrue(Hash::check('Reporter-personal-94!', $reporter->password));
        $this->assertFalse(Hash::check('Initial-default-42!', $reporter->password));
        $this->actingAs($admin)->get('/admin-only?view=accounts')
            ->assertOk()
            ->assertSee('surabaya')
            ->assertSee('Aktif');
        $this->post('/logout');

        $this->post('/login', ['login' => 'surabaya', 'password' => 'Reporter-personal-94!'])
            ->assertRedirect('/dashboard');
    }

    public function test_first_password_change_rejects_default_and_requires_matching_minimum_length_confirmation(): void
    {
        config(['sitera.default_password' => 'Initial-default-42!']);
        $reporter = $this->userWithRole('pelapor_daerah', [
            'username' => 'pending-report',
            'password' => Hash::make('Initial-default-42!'),
            'must_change_password' => true,
            'is_active' => false,
        ]);
        $this->post('/login', ['login' => 'pending-report', 'password' => 'Initial-default-42!'])
            ->assertRedirect('/password/first-login');

        $this->post('/password/first-login', [
            'password' => 'Initial-default-42!',
            'password_confirmation' => 'Initial-default-42!',
        ])->assertSessionHasErrors('password');
        $this->assertTrue($reporter->fresh()->must_change_password);
        $this->assertFalse($reporter->fresh()->is_active);

        $this->post('/password/first-login', [
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['password']);
        $this->assertTrue($reporter->fresh()->must_change_password);
        $this->assertFalse($reporter->fresh()->is_active);
    }

    public function test_disabled_non_pending_account_cannot_login(): void
    {
        $reporter = $this->userWithRole('pelapor_daerah', [
            'username' => 'disabled-report',
            'password' => Hash::make('secret-for-disabled'),
            'must_change_password' => false,
            'is_active' => false,
        ]);

        $this->post('/login', ['login' => 'disabled-report', 'password' => 'secret-for-disabled'])
            ->assertSessionHasErrors();
        $this->assertGuest();
        $this->assertFalse($reporter->fresh()->is_active);
    }

    private function userWithRole(string $code, array $attributes = []): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);

        return User::factory()->create($attributes + ['role_id' => $role->id]);
    }
}
