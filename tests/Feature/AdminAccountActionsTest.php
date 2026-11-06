<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccountActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_verify_pic_without_activating_pending_account(): void
    {
        $admin = $this->user('super_admin');
        $reporter = $this->user('pelapor_daerah', [
            'username' => 'pending-pic',
            'contact' => 'PIC Kabupaten',
            'is_active' => false,
            'must_change_password' => true,
            'pic_verified_at' => null,
            'pic_verified_by' => null,
        ]);

        $this->actingAs($admin)->post(route('admin.users.verify-pic', $reporter))
            ->assertRedirect('/admin-only?view=accounts')
            ->assertSessionHas('status');

        $this->assertNotNull($reporter->fresh()->pic_verified_at);
        $this->assertSame($admin->id, $reporter->fresh()->pic_verified_by);
        $this->assertFalse($reporter->fresh()->is_active);
        $this->assertTrue($reporter->fresh()->must_change_password);
    }

    public function test_super_admin_can_soft_delete_account_without_deleting_its_reports(): void
    {
        $admin = $this->user('super_admin');
        $reporter = $this->user('pelapor_daerah', ['username' => 'retired-reporter']);
        $report = Report::query()->create([
            'user_id' => $reporter->id,
            'nomor_tiket' => 'LP-20261105-8901',
            'status' => 'DRAF',
            'tanggal_kejadian' => '2026-11-05',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Lainnya',
            'deskripsi' => 'Laporan untuk uji retensi riwayat setelah akun dihapus.',
        ]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $reporter))
            ->assertRedirect('/admin-only?view=accounts')
            ->assertSessionHas('status');

        $this->assertSoftDeleted('users', ['id' => $reporter->id]);
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'user_id' => $reporter->id]);
        $this->assertSame($reporter->id, $report->fresh()->user->id);
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }

    public function test_super_admin_cannot_delete_their_own_or_last_active_super_admin_account(): void
    {
        $admin = $this->user('super_admin');

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }

    public function test_non_super_admin_cannot_verify_or_delete_accounts(): void
    {
        $actor = $this->user('pelapor_daerah');
        $target = $this->user('pimpinan_kementan');

        $this->actingAs($actor)->post(route('admin.users.verify-pic', $target))->assertForbidden();
        $this->actingAs($actor)->delete(route('admin.users.destroy', $target))->assertForbidden();
        $this->assertNull($target->fresh()->pic_verified_at);
        $this->assertDatabaseHas('users', ['id' => $target->id, 'deleted_at' => null]);
    }

    private function user(string $roleCode, array $attributes = []): User
    {
        $role = Role::firstOrCreate(['code' => $roleCode], ['name' => $roleCode]);

        return User::factory()->create($attributes + [
            'role_id' => $role->id,
            'password' => Hash::make('test-password'),
        ]);
    }
}
