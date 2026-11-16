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
            'email' => 'surabaya@example.test',
            'role' => 'pelapor_daerah',
            'provinsi' => 'Jawa Timur',
            'kabupaten' => 'Surabaya',
        ])->assertRedirect('/admin-only?view=accounts');

        $reporter = User::query()->where('email', 'surabaya@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('Initial-default-42!', $reporter->password));
        $this->assertTrue($reporter->must_change_password);
        $this->assertFalse($reporter->is_active);
        $this->get('/admin-only?view=accounts')
            ->assertOk()
            ->assertSee('surabaya@example.test')
            ->assertSee('Menunggu aktivasi');
        $this->post('/logout');

        $this->post('/login', ['login' => 'surabaya@example.test', 'password' => 'Initial-default-42!'])
            ->assertRedirect('/password/first-login');
        $this->get('/dashboard')->assertRedirect('/password/first-login');
        $this->get('/password/first-login')
            ->assertOk()
            ->assertSee('Verifikasi & Aktivasi Akun')
            ->assertSee('Ulangi Email');

        $this->post('/password/first-login', [
            'name' => 'Petugas Surabaya',
            'contact' => '081234567890',
            'email_confirmation' => 'surabaya@example.test',
            'password' => 'Reporter-personal-94!',
            'password_confirmation' => 'Reporter-personal-94!',
        ])->assertRedirect('/login')->assertSessionHas('status');

        $reporter->refresh();
        $this->assertFalse($reporter->must_change_password);
        $this->assertTrue($reporter->is_active);
        $this->assertSame('Petugas Surabaya', $reporter->name);
        $this->assertSame('081234567890', $reporter->contact);
        $this->assertTrue(Hash::check('Reporter-personal-94!', $reporter->password));
        $this->assertFalse(Hash::check('Initial-default-42!', $reporter->password));
        $this->actingAs($admin)->get('/admin-only?view=accounts')
            ->assertOk()
            ->assertSee('Petugas Surabaya')
            ->assertSee('Aktif');
        $this->post('/logout');

        $this->post('/login', ['login' => 'surabaya@example.test', 'password' => 'Reporter-personal-94!'])
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
            'name' => 'PIC Pending',
            'email_confirmation' => $reporter->email,
            'password' => 'Initial-default-42!',
            'password_confirmation' => 'Initial-default-42!',
        ])->assertSessionHasErrors('password');
        $this->assertTrue($reporter->fresh()->must_change_password);
        $this->assertFalse($reporter->fresh()->is_active);

        $this->post('/password/first-login', [
            'name' => 'PIC Pending',
            'email_confirmation' => $reporter->email,
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['password']);
        $this->assertTrue($reporter->fresh()->must_change_password);
        $this->assertFalse($reporter->fresh()->is_active);
    }

    public function test_first_login_rejects_a_retyped_email_that_does_not_match_the_account(): void
    {
        $reporter = $this->userWithRole('pelapor_daerah', [
            'email' => 'email-akun@example.test',
            'password' => Hash::make('Initial-default-42!'),
            'must_change_password' => true,
            'is_active' => false,
        ]);
        $originalName = $reporter->name;
        $this->post('/login', ['login' => 'email-akun@example.test', 'password' => 'Initial-default-42!'])
            ->assertRedirect('/password/first-login');

        $this->post('/password/first-login', [
            'name' => 'PIC Email Salah',
            'email_confirmation' => 'email-lain@example.test',
            'password' => 'Reporter-personal-94!',
            'password_confirmation' => 'Reporter-personal-94!',
        ])->assertSessionHasErrors('email_confirmation');

        $reporter->refresh();
        $this->assertTrue($reporter->must_change_password);
        $this->assertFalse($reporter->is_active);
        $this->assertSame($originalName, $reporter->name);
    }

    public function test_first_login_requires_pic_name_and_allows_an_empty_contact(): void
    {
        config(['sitera.default_password' => 'Initial-default-42!']);
        $reporter = $this->userWithRole('pelapor_daerah', [
            'email' => 'tanpa-kontak@example.test',
            'password' => Hash::make('Initial-default-42!'),
            'must_change_password' => true,
            'is_active' => false,
        ]);
        $this->post('/login', ['login' => 'tanpa-kontak@example.test', 'password' => 'Initial-default-42!'])
            ->assertRedirect('/password/first-login');

        $this->post('/password/first-login', [
            'email_confirmation' => 'tanpa-kontak@example.test',
            'password' => 'Reporter-personal-94!',
            'password_confirmation' => 'Reporter-personal-94!',
        ])->assertSessionHasErrors('name');

        $this->post('/password/first-login', [
            'name' => 'PIC Tanpa Kontak',
            'email_confirmation' => 'tanpa-kontak@example.test',
            'password' => 'Reporter-personal-94!',
            'password_confirmation' => 'Reporter-personal-94!',
        ])->assertRedirect('/login');

        $reporter->refresh();
        $this->assertSame('PIC Tanpa Kontak', $reporter->name);
        $this->assertNull($reporter->contact);
        $this->assertFalse($reporter->must_change_password);
        $this->assertTrue($reporter->is_active);
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
