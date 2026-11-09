<?php

namespace Tests\Feature;

use App\Models\CpclRecord;
use App\Models\Report;
use App\Models\ReportVerification;
use App\Models\Role;
use App\Models\User;
use App\Services\ReportSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportSubmissionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_report_submission_via_http_route_records_audit_and_notifies_verifiers(): void
    {
        $reporter = $this->reporter();
        $verifier = $this->verifier();

        $report = Report::create([
            'user_id' => $reporter->id,
            'nomor_tiket' => 'LP-20261101-0001',
            'status' => 'DRAF',
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan manual uji submission service',
            'sumber_isian' => 'MANUAL',
        ]);
        $report->attachments()->create([
            'path' => 'reports/test-photo.jpg',
            'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 1024,
        ]);

        $response = $this->actingAs($reporter)->post('/laporan/'.$report->id.'/kirim');

        $response->assertRedirect('/laporan');
        $response->assertSessionHas('status', 'Laporan LP-20261101-0001 berhasil dikirim.');

        $fresh = $report->fresh();
        $this->assertSame('TERKIRIM', $fresh->status);
        $this->assertNotNull($fresh->dikirim_pada);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $reporter->id,
            'action' => 'report.submitted',
            'result' => 'success',
            'subject_type' => 'report',
            'subject_id' => (string) $report->id,
        ]);
        $this->assertSame(1, \App\Models\AuditLog::where('subject_type', 'report')->where('subject_id', (string) $report->id)->count());

        // Verify notification
        $notification = $verifier->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame('laporan_baru', $notification->data['type']);
        $this->assertSame('Laporan baru masuk', $notification->data['title']);
        $this->assertSame($report->id, $notification->data['report_id']);
    }

    public function test_manual_report_submission_rejected_without_attachments(): void
    {
        $reporter = $this->reporter();

        $report = Report::create([
            'user_id' => $reporter->id,
            'nomor_tiket' => 'LP-20261101-0002',
            'status' => 'DRAF',
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan manual tanpa lampiran',
            'sumber_isian' => 'MANUAL',
        ]);

        $response = $this->actingAs($reporter)->post('/laporan/'.$report->id.'/kirim');

        $response->assertStatus(422);
        $this->assertSame('DRAF', $report->fresh()->status);
        $this->assertDatabaseMissing('audit_logs', ['subject_id' => (string) $report->id]);
    }

    public function test_package_report_submitted_directly_via_store(): void
    {
        Storage::fake('local');
        $reporter = $this->reporter();
        $verifier = $this->verifier();
        $cpcl = $this->cpcl('Jawa Barat', 'Subang');

        $response = $this->actingAs($reporter)->post('/laporan/buat/'.$cpcl->id, [
            '_action' => 'submit',
            'bast' => UploadedFile::fake()->create('bast.pdf', 100, 'application/pdf'),
            'lpj' => UploadedFile::fake()->create('lpj.pdf', 100, 'application/pdf'),
            'dokumentasi' => UploadedFile::fake()->create('doc.pptx', 100, 'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
        ]);

        $response->assertRedirect('/laporan');
        $report = Report::where('user_id', $reporter->id)->where('cpcl_id', $cpcl->id)->firstOrFail();
        $this->assertSame('TERKIRIM', $report->status);
        $this->assertNotNull($report->dikirim_pada);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $reporter->id,
            'action' => 'report.submitted',
            'result' => 'success',
            'subject_id' => (string) $report->id,
        ]);

        $notification = $verifier->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame('laporan_baru', $notification->data['type']);
    }

    public function test_package_report_submitted_via_update(): void
    {
        Storage::fake('local');
        $reporter = $this->reporter();
        $cpcl = $this->cpcl('Jawa Barat', 'Subang');

        // Create draft first
        $this->actingAs($reporter)->post('/laporan/buat/'.$cpcl->id, [
            '_action' => 'draft',
            'bast' => UploadedFile::fake()->create('bast.pdf', 100, 'application/pdf'),
            'lpj' => UploadedFile::fake()->create('lpj.pdf', 100, 'application/pdf'),
            'dokumentasi' => UploadedFile::fake()->create('doc.pptx', 100, 'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
        ])->assertRedirect('/laporan');

        $report = Report::where('user_id', $reporter->id)->where('cpcl_id', $cpcl->id)->firstOrFail();
        $this->assertSame('DRAF', $report->status);

        // Now submit via update
        $response = $this->actingAs($reporter)->put('/laporan/'.$report->id.'/paket', [
            '_action' => 'submit',
        ]);

        $response->assertRedirect('/laporan');
        $this->assertSame('TERKIRIM', $report->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $reporter->id,
            'action' => 'report.submitted',
            'subject_id' => (string) $report->id,
        ]);
    }

    public function test_package_report_rejected_if_any_required_document_missing(): void
    {
        Storage::fake('local');
        $reporter = $this->reporter();
        $cpcl = $this->cpcl('Jawa Barat', 'Subang');

        // Create draft with only BAST and LPJ (missing DOKUMENTASI)
        $report = Report::create([
            'user_id' => $reporter->id,
            'cpcl_id' => $cpcl->id,
            'nomor_tiket' => 'LP-20261101-0005',
            'status' => 'DRAF',
            'versi' => 1,
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Paket CPCL',
            'deskripsi' => 'Paket tidak lengkap',
            'sumber_isian' => 'MANUAL',
        ]);
        $report->attachments()->create([
            'kategori' => 'BAST',
            'versi' => 1,
            'path' => 'reports/bast.pdf',
            'original_name' => 'bast.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
        ]);
        $report->attachments()->create([
            'kategori' => 'LPJ',
            'versi' => 1,
            'path' => 'reports/lpj.pdf',
            'original_name' => 'lpj.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
        ]);

        $response = $this->actingAs($reporter)->post('/laporan/'.$report->id.'/kirim');

        $response->assertStatus(422);
        $this->assertSame('DRAF', $report->fresh()->status);
    }

    public function test_correction_submission_records_audit_and_koreksi_dikirim_notification(): void
    {
        $reporter = $this->reporter();
        $verifier = $this->verifier();

        $original = Report::create([
            'user_id' => $reporter->id,
            'nomor_tiket' => 'LP-20261101-0008',
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => '2026-10-28',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan awal',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);
        ReportVerification::create([
            'report_id' => $original->id,
            'verifier_id' => $verifier->id,
            'hasil' => 'TIDAK_SESUAI',
            'catatan' => 'Perbaiki',
            'diperiksa_pada' => now(),
        ]);

        $correction = Report::create([
            'user_id' => $reporter->id,
            'koreksi_dari' => $original->id,
            'nomor_tiket' => 'LP-20261101-0009',
            'status' => 'DRAF',
            'tanggal_kejadian' => '2026-10-28',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan koreksi lengkap',
            'sumber_isian' => 'MANUAL',
        ]);
        $correction->attachments()->create([
            'path' => 'reports/corr.jpg',
            'original_name' => 'corr.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 500,
        ]);

        $response = $this->actingAs($reporter)->post('/laporan/'.$correction->id.'/koreksi/kirim');

        $response->assertRedirect('/laporan');
        $response->assertSessionHas('status', 'Koreksi LP-20261101-0009 berhasil dikirim.');

        $this->assertSame('TERKIRIM', $correction->fresh()->status);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $reporter->id,
            'action' => 'report.submitted',
            'result' => 'success',
            'subject_id' => (string) $correction->id,
        ]);

        // Verify notification type is koreksi_dikirim
        $notification = $verifier->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame('koreksi_dikirim', $notification->data['type']);
        $this->assertSame('Koreksi laporan baru', $notification->data['title']);
    }

    public function test_cross_user_submission_is_forbidden_or_not_found(): void
    {
        $owner = $this->reporter();
        $other = User::factory()->create(['role_id' => Role::where('code', 'pelapor_daerah')->value('id')]);

        $report = Report::create([
            'user_id' => $owner->id,
            'nomor_tiket' => 'LP-20261101-0012',
            'status' => 'DRAF',
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan privat',
            'sumber_isian' => 'MANUAL',
        ]);

        $this->actingAs($other)->post('/laporan/'.$report->id.'/kirim')
            ->assertNotFound();

        $this->assertSame('DRAF', $report->fresh()->status);
    }

    public function test_double_submission_is_prevented_and_does_not_duplicate_audits_or_notifications(): void
    {
        $reporter = $this->reporter();
        $verifier = $this->verifier();

        $report = Report::create([
            'user_id' => $reporter->id,
            'nomor_tiket' => 'LP-20261101-0015',
            'status' => 'DRAF',
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan uji double submit',
            'sumber_isian' => 'MANUAL',
        ]);
        $report->attachments()->create([
            'path' => 'reports/sample.jpg',
            'original_name' => 'sample.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 100,
        ]);

        // First submission succeeds
        $this->actingAs($reporter)->post('/laporan/'.$report->id.'/kirim')->assertRedirect('/laporan');
        $this->assertSame('TERKIRIM', $report->fresh()->status);
        $this->assertSame(1, \App\Models\AuditLog::where('subject_id', (string) $report->id)->count());
        $this->assertSame(1, $verifier->notifications()->count());

        // Second submission fails with 422
        $this->actingAs($reporter)->post('/laporan/'.$report->id.'/kirim')->assertStatus(422);

        // Counts must still be exactly 1
        $this->assertSame(1, \App\Models\AuditLog::where('subject_id', (string) $report->id)->count());
        $this->assertSame(1, $verifier->notifications()->count());
    }

    private function reporter(): User
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        return User::factory()->create([
            'role_id' => $role->id,
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
        ]);
    }

    private function verifier(): User
    {
        $role = Role::firstOrCreate(['code' => 'verifikator_pusat'], ['name' => 'Verifikator Pusat']);
        return User::factory()->create(['role_id' => $role->id]);
    }

    private function cpcl(string $provinsi, string $kabupaten): CpclRecord
    {
        return CpclRecord::create([
            'no' => 'CPCL-INT-'.bin2hex(random_bytes(3)),
            'nama_di' => 'DI Ciasem',
            'provinsi' => $provinsi,
            'kabupaten' => $kabupaten,
            'kecamatan' => 'Pamanukan',
            'desa' => 'Mulyasari',
            'kel_tani' => 'P3A Mandiri',
            'aktif' => true,
        ]);
    }
}
