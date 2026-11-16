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

    public function test_super_admin_can_create_a_reporter_account_with_email_only(): void
    {
        config(['sitera.default_password' => 'Local-test-default-22!']);
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)->post('/admin/users', [
            'email' => 'pelapor@example.test',
            'role' => 'pelapor_daerah',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
        ])->assertRedirect('/admin-only?view=accounts');

        $user = User::where('email', 'pelapor@example.test')->firstOrFail();
        $this->assertSame('pelapor_daerah', $user->role->code);
        $this->assertNull($user->username);
        $this->assertSame('Pengguna Baru', $user->name);
        $this->assertNull($user->contact);
        $this->assertSame('Jawa Barat', $user->provinsi);
        $this->assertSame('Subang', $user->kabupaten);
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
            'role' => 'pelapor_daerah',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::query()->count());
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

    public function test_account_creation_form_only_asks_for_role_region_and_email(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)->get('/admin-only?view=accounts')
            ->assertOk()
            ->assertSee('Buat akun pengguna')
            ->assertSee('name="role"', false)
            ->assertSee('name="provinsi"', false)
            ->assertSee('name="kabupaten"', false)
            ->assertSee('name="email"', false)
            ->assertDontSee('name="username"', false)
            ->assertDontSee('name="contact"', false)
            ->assertDontSee('name="name"', false);
    }

    public function test_account_table_shows_pic_and_contact_filled_during_activation(): void
    {
        $admin = $this->userWithRole('super_admin');
        $reporterRole = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);

        User::factory()->create([
            'role_id' => $reporterRole->id,
            'name' => 'Siti Aminah',
            'contact' => '081298765432',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        User::factory()->create([
            'role_id' => $reporterRole->id,
            'name' => 'Budi Tanpa Kontak',
            'contact' => null,
            'must_change_password' => false,
        ]);
        User::factory()->create([
            'role_id' => $reporterRole->id,
            'name' => 'Pengguna Baru',
            'contact' => null,
            'must_change_password' => true,
            'is_active' => false,
        ]);

        $this->actingAs($admin)->get('/admin-only?view=accounts')
            ->assertOk()
            ->assertSee('<th>PIC</th>', false)
            ->assertSee('<th>Kontak HP</th>', false)
            ->assertDontSee('Pengguna / PIC')
            ->assertSee('Siti Aminah')
            ->assertSee('081298765432')
            ->assertSee('Budi Tanpa Kontak')
            ->assertSee('Tidak diisi')
            ->assertSee('Belum diaktivasi')
            ->assertDontSee('Pengguna Baru');
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
