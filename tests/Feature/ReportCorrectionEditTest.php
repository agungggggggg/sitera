<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportVerification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportCorrectionEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_correction_draft(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $original = $this->submittedReport($owner, 'LP-20261101-0001');
        ReportVerification::create([
            'report_id' => $original->id,
            'verifier_id' => $this->userWithRole('verifikator_pusat')->id,
            'hasil' => 'TIDAK_SESUAI',
            'catatan' => 'Perjelas deskripsi.',
            'diperiksa_pada' => now(),
        ]);
        $correction = $this->actingAs($owner)->post('/laporan/'.$original->id.'/koreksi')->getSession()->get('status')
            ? Report::where('koreksi_dari', $original->id)->firstOrFail()
            : Report::where('koreksi_dari', $original->id)->firstOrFail();

        $this->actingAs($owner)
            ->put('/laporan/'.$correction->id.'/koreksi', [
                'tanggal_kejadian' => '2026-10-31',
                'provinsi' => 'Jawa Barat',
                'kabupaten' => 'Indramayu',
                'kecamatan' => 'Jatibarang',
                'desa' => 'Sukalila',
                'nama_saluran' => 'Saluran Sekunder Baru',
                'jenis_kerusakan' => 'Bendung',
                'deskripsi' => 'Deskripsi koreksi yang sudah diperjelas.',
                'panjang_terdampak_m' => '7.50',
                'luas_terdampak_ha' => '3.25',
                'koordinat' => '-6.300000, 107.900000',
                'catatan' => 'Koreksi sudah dilengkapi.',
            ])
            ->assertRedirect('/laporan/'.$correction->id.'/koreksi/edit')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('reports', [
            'id' => $correction->id,
            'status' => 'DRAF',
            'kabupaten' => 'Indramayu',
            'jenis_kerusakan' => 'Bendung',
        ]);
        $this->assertDatabaseHas('reports', [
            'id' => $original->id,
            'kabupaten' => 'Subang',
        ]);
    }

    public function test_other_user_cannot_update_correction(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $other = User::factory()->create(['role_id' => Role::where('code', 'pelapor_daerah')->value('id')]);
        $original = $this->submittedReport($owner, 'LP-20261101-0002');
        ReportVerification::create([
            'report_id' => $original->id,
            'verifier_id' => $this->userWithRole('verifikator_pusat')->id,
            'hasil' => 'TIDAK_SESUAI',
            'catatan' => 'Perlu koreksi.',
            'diperiksa_pada' => now(),
        ]);
        $correction = Report::create([
            'user_id' => $owner->id,
            'koreksi_dari' => $original->id,
            'nomor_tiket' => 'LP-20261101-0003',
            'status' => 'DRAF',
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Koreksi.',
            'catatan' => 'Koreksi dari LP-20261101-0002',
            'sumber_isian' => 'MANUAL',
        ]);

        $this->actingAs($other)->put('/laporan/'.$correction->id.'/koreksi', [
            'tanggal_kejadian' => '2026-10-31',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Bendung',
            'deskripsi' => 'Percobaan tidak sah.',
        ])->assertNotFound();
    }

    public function test_submitted_correction_cannot_be_updated(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $correction = Report::create([
            'user_id' => $owner->id,
            'koreksi_dari' => null,
            'nomor_tiket' => 'LP-20261101-0004',
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Bendung',
            'deskripsi' => 'Sudah terkirim.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);

        $this->actingAs($owner)->put('/laporan/'.$correction->id.'/koreksi', [
            'tanggal_kejadian' => '2026-10-31',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Bendung',
            'deskripsi' => 'Tidak boleh berubah.',
        ])->assertNotFound();
    }

    private function submittedReport(User $user, string $ticket): Report
    {
        return Report::create([
            'user_id' => $user->id,
            'nomor_tiket' => $ticket,
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan awal.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);
        return User::factory()->create(['role_id' => $role->id]);
    }
}
