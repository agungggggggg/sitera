<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use App\Notifications\SiteraEventNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NotificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_submission_notifies_all_verifiers(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $verifier = $this->userWithRole('verifikator_pusat');
        $report = $this->reportFor($owner, 'DRAF');
        $report->attachments()->create([
            'path' => 'tests/evidence.jpg',
            'original_name' => 'evidence.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 100,
        ]);

        $this->actingAs($owner)->post('/laporan/'.$report->id.'/kirim')->assertRedirect('/laporan');

        $notification = $verifier->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame('laporan_baru', $notification->data['type']);
        $this->assertSame($report->id, $notification->data['report_id']);
    }

    public function test_verification_notifies_report_owner(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $verifier = $this->userWithRole('verifikator_pusat');
        $report = $this->reportFor($owner, 'TERKIRIM');

        $this->actingAs($verifier)->post('/laporan/'.$report->id.'/verifikasi', [
            'hasil' => 'TIDAK_SESUAI',
            'catatan' => 'Mohon lengkapi lokasi.',
        ])->assertRedirect('/verifikasi/'.$report->id);

        $notification = $owner->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame('hasil_verifikasi', $notification->data['type']);
        $this->assertSame('TIDAK_SESUAI', $notification->data['hasil']);
    }

    public function test_correction_submission_notifies_verifiers(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $verifier = $this->userWithRole('verifikator_pusat');
        $original = $this->reportFor($owner, 'TERKIRIM');
        $original->verification()->create([
            'verifier_id' => $verifier->id,
            'hasil' => 'TIDAK_SESUAI',
            'catatan' => 'Mohon koreksi.',
            'diperiksa_pada' => now(),
        ]);
        $correction = $this->reportFor($owner, 'DRAF');
        $correction->update(['koreksi_dari' => $original->id]);
        $correction->attachments()->create([
            'path' => 'tests/correction.jpg',
            'original_name' => 'correction.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 100,
        ]);

        $this->actingAs($owner)->post('/laporan/'.$correction->id.'/koreksi/kirim')->assertRedirect('/laporan');

        $notification = $verifier->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame('koreksi_dikirim', $notification->data['type']);
        $this->assertSame($correction->id, $notification->data['report_id']);
    }

    public function test_user_can_see_only_own_notifications_and_mark_them_read(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $other = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'TERKIRIM');
        $owner->notify(new SiteraEventNotification('uji', 'Notifikasi saya', 'Isi notifikasi.', $report->id, $report->nomor_tiket));
        $other->notify(new SiteraEventNotification('uji', 'Notifikasi privat', 'Jangan tampil.', $report->id, $report->nomor_tiket));
        $mine = $owner->notifications()->firstOrFail();
        $theirs = $other->notifications()->firstOrFail();

        $this->actingAs($owner)->get('/notifikasi')
            ->assertOk()
            ->assertSee('Notifikasi saya')
            ->assertDontSee('Notifikasi privat');

        $this->actingAs($owner)->post('/notifikasi/'.$mine->id.'/baca')->assertRedirect('/notifikasi');
        $this->assertNotNull($owner->notifications()->findOrFail($mine->id)->read_at);
        $this->actingAs($owner)->post('/notifikasi/'.$theirs->id.'/baca')->assertNotFound();
    }

    private function reportFor(User $owner, string $status): Report
    {
        return Report::create([
            'user_id' => $owner->id,
            'nomor_tiket' => 'LP-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => $status,
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan notifikasi.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => $status === 'TERKIRIM' ? now() : null,
        ]);
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
