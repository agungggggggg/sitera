<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_page_uses_sitera_workspace_ui(): void
    {
        $role = Role::create(['code' => 'super_admin', 'name' => 'Super Admin']);
        $reporterRole = Role::create(['code' => 'pelapor_daerah', 'name' => 'Pelapor Daerah']);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $target = User::factory()->create(['name' => 'Pelapor Contoh', 'username' => 'contoh-daerah', 'email' => 'pelapor@example.test', 'contact' => 'PIC Contoh', 'role_id' => $reporterRole->id]);

        $this->actingAs($admin)
            ->get('/admin-only')
            ->assertOk()
            ->assertSee('Manajemen Akun')
            ->assertSee('pelapor@example.test')
            ->assertSee('/sitera/superadmin-review.css')
            ->assertSee('/sitera/sitera-logo.webp')
            ->assertSee('Catat calon pengguna, verifikasi PIC')
            ->assertSee('Username')
            ->assertSee('name="email" type="email"', false)
            ->assertSee('flex-wrap:nowrap', false)
            ->assertSee('Reset akses')
            ->assertSee('Verifikasi PIC')
            ->assertSee('Menunggu verifikasi PIC')
            ->assertSee('× Hapus')
            ->assertSee('Nonaktifkan')
            ->assertSee('Akun nonaktif ditolak');
    }

    public function test_audit_log_view_has_operational_read_only_visual_hierarchy(): void
    {
        $role = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
        $admin = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($admin)->get(route('admin.index', ['view' => 'audit']))
            ->assertOk()
            ->assertSee('Audit log')
            ->assertSee('Pembaruan ·')
            ->assertSee('Mode audit operasional.')
            ->assertSee('dicatat oleh server')
            ->assertSee('Hanya baca')
            ->assertSee('Cari aktor, objek, tindakan, atau hasil')
            ->assertSee('Semua tindakan')
            ->assertSee('Semua hasil')
            ->assertSee('Reset')
            ->assertSee('Telusuri tindakan administratif tanpa mengubah catatan audit.')
            ->assertSee('WAKTU')
            ->assertSee('AKTOR')
            ->assertSee('TINDAKAN')
            ->assertSee('OBJEK')
            ->assertSee('HASIL')
            ->assertSee('RINCIAN')
            ->assertSee('date_from')
            ->assertSee('date_to');
    }

    public function test_audit_log_table_restores_reference_columns_and_human_readable_rows(): void
    {
        $role = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
        $admin = User::factory()->create(['name' => 'Super Admin Uji', 'role_id' => $role->id]);
        AuditLog::query()->create([
            'actor_id' => $admin->id,
            'action' => 'user.password_reset',
            'result' => 'success',
            'subject_type' => 'user',
            'subject_id' => '7',
            'metadata' => [],
        ]);

        $this->actingAs($admin)->get(route('admin.index', ['view' => 'audit']))
            ->assertOk()
            ->assertSee('WAKTU')
            ->assertSee('AKTOR')
            ->assertSee('TINDAKAN')
            ->assertSee('OBJEK')
            ->assertSee('HASIL')
            ->assertSee('RINCIAN')
            ->assertSee('Reset akses akun')
            ->assertSee('AK-007')
            ->assertSee('Berhasil')
            ->assertSee('Password awal diatur ulang; pengguna wajib mengganti password saat login berikutnya.')
            ->assertSeeInOrder(['WAKTU', 'AKTOR', 'TINDAKAN', 'OBJEK', 'HASIL', 'RINCIAN']);
    }

    public function test_super_admin_features_are_separate_review_screens_and_account_filters_are_real(): void
    {
        $role = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $reporterRole = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        User::factory()->create(['name' => 'Cari Saya', 'username' => 'cari-saya', 'contact' => 'PIC Subang', 'email' => 'find@example.test', 'role_id' => $reporterRole->id, 'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang']);
        User::factory()->create(['name' => 'Akun Lain', 'email' => 'other@example.test', 'role_id' => $reporterRole->id, 'provinsi' => 'Jawa Barat', 'kabupaten' => 'Indramayu']);

        $this->actingAs($admin)->get(route('admin.index', ['view' => 'accounts', 'q' => 'Subang']))
            ->assertOk()->assertSee('find@example.test')->assertDontSee('other@example.test');

        $this->actingAs($admin)->get(route('admin.index', ['view' => 'accounts', 'status' => 'pic_pending']))
            ->assertOk()->assertSee('find@example.test')->assertDontSee('other@example.test');

        $this->actingAs($admin)->get(route('admin.index', ['view' => 'cpcl']))
            ->assertOk()->assertSee('Referensi CPCL')->assertSee('Tambah CPCL')->assertDontSee('Impor data CPCL');

        $this->actingAs($admin)->get(route('admin.index', ['view' => 'imports']))
            ->assertOk()->assertSee('Impor data CPCL')->assertSee('Pratinjau hasil');
    }
}
