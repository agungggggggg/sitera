<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CpclRecord;
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
            ->assertSee('<th>Email</th>', false)
            ->assertSee('name="email" type="email"', false)
            ->assertSee('flex-wrap:nowrap', false)
            ->assertSee('Reset akses')
            ->assertSee('Verifikasi PIC')
            ->assertSee('Menunggu verifikasi PIC')
            ->assertSee('Hapus akun')
            ->assertSee('Nonaktifkan')
            ->assertSee('acc-status', false)
            ->assertSee('delete-action', false)
            ->assertSee('data-delete-account-form', false)
            ->assertSee('account-delete-modal', false)
            ->assertSee('account-delete-back', false)
            ->assertSee('Yakin mau menghapus Akun')
            ->assertSee('Akun nonaktif ditolak');
    }

    public function test_audit_log_view_has_operational_read_only_visual_hierarchy(): void
    {
        $role = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
        $admin = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($admin)->get(route('admin.index', ['view' => 'audit']))
            ->assertOk()
            ->assertSee('Audit Log')
            ->assertSee('Ruang Administrasi')
            ->assertSee('Telusuri tindakan administratif tanpa mengubah catatan audit.')
            ->assertSee('Riwayat tindakan administratif. Tampilan ini hanya-baca.')
            ->assertSee('Cari aktor, objek, tindakan, atau hasil')
            ->assertSee('Semua tindakan')
            ->assertSee('Semua hasil')
            ->assertSee('Reset')
            ->assertSee('WAKTU')
            ->assertSee('AKTOR')
            ->assertSee('TINDAKAN')
            ->assertSee('OBJEK')
            ->assertSee('HASIL')
            ->assertSee('RINCIAN')
            ->assertDontSee('Mode audit operasional.')
            ->assertDontSee('Hanya baca')
            ->assertDontSee('Pembaruan ·');
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
            ->assertOk()
            ->assertSee('Tambah CPCL secara manual')
            ->assertSee('Referensi CPCL')
            ->assertSee('Tambah CPCL')
            ->assertSee('Simpan CPCL')
            ->assertSee('Batal')
            ->assertSee('Belum ada data CPCL operasional yang sesuai dengan filter.')
            ->assertSee('id="cpcl-detail-modal"', false)
            ->assertDontSee('Impor data CPCL');

        $this->actingAs($admin)->get(route('admin.index', ['view' => 'imports']))
            ->assertOk()
            ->assertSee('Impor data CPCL')
            ->assertSee('Template Excel (.xlsx)')
            ->assertSee('Pilih file CSV')
            ->assertSee('Validasi &amp; Pratinjau', false)
            ->assertSee('Konfirmasi Penambahan')
            ->assertDontSee('Template CSV')
            ->assertDontSee('Template XLSX');
    }

    public function test_cpcl_table_renders_detail_and_delete_actions_for_each_record(): void
    {
        $role = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $cpcl = CpclRecord::create([
            'no' => '1',
            'nama_di' => 'DI Contoh Barat',
            'kewenangan' => 'Kabupaten/Kota',
            'keterangan' => 'Data sumber',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan',
            'desa' => 'Mulyasari',
            'kel_tani' => 'P3A Tirta Contoh',
            'nama_ketua' => 'Ketua Uji',
            'realisasi' => '1',
            'luas_ha' => 25.00,
            'x' => 107.1234,
            'y' => -6.1234,
        ]);

        $this->actingAs($admin)->get(route('admin.index', ['view' => 'cpcl']))
            ->assertOk()
            ->assertSeeInOrder(['NO', 'NAMA DI', 'KEWENANGAN', 'KETERANGAN', 'PROVINSI', 'KAB/KOTA', 'KECAMATAN', 'DESA/KELURAHAN', 'KELOMPOK TANI', 'AKSI'])
            ->assertSee('DI Contoh Barat')
            ->assertSee('CPCL #'.$cpcl->id)
            ->assertSee('P3A Tirta Contoh')
            ->assertSee('data-cpcl-detail=', false)
            ->assertSee('action="'.route('admin.cpcl.destroy', $cpcl).'"', false)
            ->assertSee('Detail')
            ->assertSee('Hapus');
    }
}
