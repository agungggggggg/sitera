<?php

namespace Tests\Feature;

use App\Models\CpclRecord;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use App\Notifications\SiteraEventNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UiConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_sitera_pagination_renders_bounded_svg_arrows_without_tailwind_classes(): void
    {
        $reporter = $this->userWithRole('pelapor_daerah', [
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
        ]);

        for ($i = 1; $i <= 22; $i++) {
            CpclRecord::create([
                'no' => (string) $i,
                'nama_di' => 'DI Pamanukan '.$i,
                'provinsi' => 'Jawa Barat',
                'kabupaten' => 'Subang',
                'kecamatan' => 'Pamanukan',
                'desa' => 'Desa '.$i,
                'kel_tani' => 'P3A Tirta '.$i,
            ]);
        }

        $this->actingAs($reporter)->get(route('reports.create'))
            ->assertOk()
            ->assertSee('class="sitera-pagination"', false)
            ->assertSee('width="14" height="14"', false)
            ->assertSee('Menampilkan 1')
            ->assertDontSee('w-5 h-5', false)
            ->assertDontSee('Showing', false);
    }

    public function test_notification_bell_uses_svg_and_renders_unread_badge(): void
    {
        $reporter = $this->userWithRole('pelapor_daerah', [
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
        ]);
        $report = $this->reportFor($reporter);
        $reporter->notify(new SiteraEventNotification(
            'hasil_verifikasi',
            'Laporan diperiksa',
            'Hasil pemeriksaan tersedia.',
            $report->id,
            $report->nomor_tiket,
        ));

        $this->actingAs($reporter)->get('/dashboard')
            ->assertOk()
            ->assertSee('class="unread-badge"', false)
            ->assertSee('viewBox="0 0 24 24"', false);

        $this->actingAs($reporter)->get('/notifikasi')
            ->assertOk()
            ->assertSee('notification-link', false)
            ->assertSee('viewBox="0 0 24 24"', false)
            ->assertDontSee('♧', false);
    }

    public function test_package_form_validation_errors_do_not_show_duplicate_filter_error_banner(): void
    {
        $reporter = $this->userWithRole('pelapor_daerah', [
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
        ]);
        $cpcl = CpclRecord::create([
            'nama_di' => 'DI Pamanukan',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan',
            'desa' => 'Mulyasari',
            'kel_tani' => 'P3A Tirta',
        ]);

        $this->actingAs($reporter)
            ->from(route('reports.package.create', $cpcl))
            ->followingRedirects()
            ->post(route('reports.package.store', $cpcl), ['_action' => 'submit'])
            ->assertOk()
            ->assertSee('Periksa kembali berkas pelaporan.')
            ->assertDontSee('Periksa pilihan filter.');
    }

    public function test_verifier_review_validation_errors_do_not_show_duplicate_filter_error_banner(): void
    {
        $reporter = $this->userWithRole('pelapor_daerah');
        $verifier = $this->userWithRole('verifikator_pusat');
        $report = $this->reportFor($reporter);

        $this->actingAs($verifier)
            ->from(route('verification.show', $report))
            ->followingRedirects()
            ->post(route('reports.verify', $report), [
                'hasil' => 'TIDAK_SESUAI',
                'catatan' => '',
            ])
            ->assertOk()
            ->assertSee('Periksa kembali hasil pemeriksaan.')
            ->assertDontSee('Periksa pilihan filter.');
    }

    public function test_admin_audit_view_validation_errors_show_only_one_error_banner(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)
            ->from(route('admin.index', ['view' => 'audit']))
            ->followingRedirects()
            ->get(route('admin.index', ['view' => 'audit', 'result' => 'invalid_result']))
            ->assertOk()
            ->assertSee('Periksa kembali:')
            ->assertDontSee('Periksa filter:');
    }

    private function reportFor(User $owner): Report
    {
        return Report::create([
            'user_id' => $owner->id,
            'nomor_tiket' => 'LP-'.now()->format('Ymd').'-0001',
            'status' => 'TERKIRIM',
            'versi' => 1,
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Uji konsistensi UI.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function userWithRole(string $code, array $attributes = []): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);

        return User::factory()->create(array_merge([
            'role_id' => $role->id,
            'is_active' => true,
            'must_change_password' => false,
        ], $attributes));
    }
}
