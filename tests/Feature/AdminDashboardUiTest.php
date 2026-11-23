<?php

namespace Tests\Feature;

use App\Models\CpclRecord;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminDashboardUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_uses_demo_monitor_operate_composition_with_real_counts(): void
    {
        $adminRole = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
        $reporterRole = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        User::factory()->create(['role_id' => $reporterRole->id, 'name' => 'Petugas Subang']);
        CpclRecord::create([
            'nama_di' => 'DI Pamanukan', 'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan', 'desa' => 'Mulyasari', 'kel_tani' => 'P3A Tirta',
        ]);

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('Ringkasan administrasi')
            ->assertSee('/sitera/theme.css')
            ->assertSee('/sitera/pelapor-review.css')
            ->assertSee('/sitera/superadmin-review.css')
            ->assertSee('/sitera/sitera-logo.webp')
            ->assertSee('heading-actions', false)
            ->assertSee('Impor CPCL')
            ->assertSee('Tambah akun')
            ->assertSee('Manajemen Akun')
            ->assertSee('Rekap Status CPCL')
            ->assertSee('Total referensi CPCL')
            ->assertSee('<strong>1</strong>', false)
            ->assertSee('DI Pamanukan')
            ->assertDontSee('1.840', false)
            ->assertDontSee('Data simulasi');
    }

    public function test_super_admin_dashboard_is_not_shown_to_reporter(): void
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        $reporter = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($reporter)->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Rekap Status CPCL')
            ->assertSee('Buat Laporan');
    }

    public function test_super_admin_donut_uses_status_specific_color_when_earlier_buckets_are_zero(): void
    {
        $adminRole = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
        $reporterRole = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        $verifierRole = Role::firstOrCreate(['code' => 'verifikator_pusat'], ['name' => 'Verifikator Pusat']);

        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $reporter = User::factory()->create(['role_id' => $reporterRole->id, 'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang']);
        $verifier = User::factory()->create(['role_id' => $verifierRole->id]);

        $cpcl = CpclRecord::create([
            'nama_di' => 'DI Pamanukan',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan',
            'desa' => 'Mulyasari',
            'kel_tani' => 'P3A Tirta',
        ]);

        $report = Report::create([
            'user_id' => $reporter->id,
            'cpcl_id' => $cpcl->id,
            'nomor_tiket' => 'LP-20261123-0001',
            'status' => 'TERKIRIM',
            'versi' => 1,
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan',
            'desa' => 'Mulyasari',
            'nama_saluran' => 'DI Pamanukan',
            'jenis_kerusakan' => 'Paket CPCL',
            'deskripsi' => 'Laporan sesuai.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);

        $report->verification()->create([
            'verifier_id' => $verifier->id,
            'hasil' => 'SESUAI',
            'catatan' => 'Lengkap.',
            'diperiksa_pada' => now(),
        ]);

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('stroke="#39845a"', false)
            ->assertDontSee('stroke="#879ba5"', false);
    }
}
