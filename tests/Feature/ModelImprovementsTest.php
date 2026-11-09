<?php

namespace Tests\Feature;

use App\Models\CpclImportBatch;
use App\Models\CpclRecord;
use App\Models\Report;
use App\Models\ReportAttachment;
use App\Models\ReportVerification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ModelImprovementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cpcl_record_can_access_creator_even_when_creator_is_soft_deleted(): void
    {
        $admin = $this->userWithRole('super_admin');
        $cpcl = CpclRecord::create([
            'no' => 'CPCL-TEST-1',
            'nama_di' => 'DI Ciasem',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan',
            'desa' => 'Mulyasari',
            'kel_tani' => 'P3A Mandiri',
            'aktif' => true,
            'created_by' => $admin->id,
        ]);

        $this->assertNotNull($cpcl->createdBy);
        $this->assertSame($admin->id, $cpcl->createdBy->id);

        // Soft delete the creator admin
        $admin->delete();
        $this->assertNotNull($admin->fresh()->deleted_at);

        // With withTrashed(), relationship must still return the user
        $freshCpcl = $cpcl->fresh();
        $this->assertNotNull($freshCpcl->createdBy);
        $this->assertSame($admin->id, $freshCpcl->createdBy->id);
        $this->assertTrue($freshCpcl->createdBy->trashed());
    }

    public function test_cpcl_import_batch_can_access_user_even_when_user_is_soft_deleted(): void
    {
        $admin = $this->userWithRole('super_admin');
        $batch = CpclImportBatch::create([
            'user_id' => $admin->id,
            'source_name' => 'import_cpcl.xlsx',
            'rows' => [['nama_di' => 'DI Test']],
            'summary' => ['Baru valid' => 1],
            'confirmed_at' => now(),
        ]);

        $this->assertNotNull($batch->user);
        $this->assertSame($admin->id, $batch->user->id);

        // Soft delete the importer admin
        $admin->delete();
        $this->assertNotNull($admin->fresh()->deleted_at);

        // With withTrashed(), relationship must still return the user
        $freshBatch = $batch->fresh();
        $this->assertNotNull($freshBatch->user);
        $this->assertSame($admin->id, $freshBatch->user->id);
        $this->assertTrue($freshBatch->user->trashed());
    }

    public function test_report_versi_is_cast_to_integer(): void
    {
        $user = $this->userWithRole('pelapor_daerah');
        $report = Report::create([
            'user_id' => $user->id,
            'nomor_tiket' => 'LP-20261101-0001',
            'status' => 'DRAF',
            'versi' => '3', // string passed
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Uji integer cast report',
            'sumber_isian' => 'MANUAL',
        ]);

        $fresh = $report->fresh();
        $this->assertIsInt($fresh->versi);
        $this->assertSame(3, $fresh->versi);
    }

    public function test_report_attachment_versi_and_size_bytes_are_cast_to_integer(): void
    {
        $user = $this->userWithRole('pelapor_daerah');
        $report = Report::create([
            'user_id' => $user->id,
            'nomor_tiket' => 'LP-20261101-0002',
            'status' => 'DRAF',
            'versi' => 1,
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Uji integer cast attachment',
            'sumber_isian' => 'MANUAL',
        ]);

        $attachment = $report->attachments()->create([
            'kategori' => 'BAST',
            'versi' => '2', // string passed
            'path' => 'reports/bast.pdf',
            'original_name' => 'bast.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => '4096', // string passed
        ]);

        $freshAttachment = $attachment->fresh();
        $this->assertIsInt($freshAttachment->versi);
        $this->assertSame(2, $freshAttachment->versi);
        $this->assertIsInt($freshAttachment->size_bytes);
        $this->assertSame(4096, $freshAttachment->size_bytes);
    }

    public function test_correction_edit_eager_loads_original_report_verification_and_attachments(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $verifier = $this->userWithRole('verifikator_pusat');

        $original = Report::create([
            'user_id' => $owner->id,
            'nomor_tiket' => 'LP-20261101-0010',
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => '2026-10-25',
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
            'catatan' => 'Perbaiki lampiran dan penjelasan.',
            'diperiksa_pada' => now(),
        ]);

        $correction = Report::create([
            'user_id' => $owner->id,
            'koreksi_dari' => $original->id,
            'nomor_tiket' => 'LP-20261101-0011',
            'status' => 'DRAF',
            'tanggal_kejadian' => '2026-10-25',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan koreksi draf',
            'sumber_isian' => 'MANUAL',
            'catatan' => 'Koreksi dari '.$original->nomor_tiket,
        ]);
        $correction->attachments()->create([
            'path' => 'reports/corr.jpg',
            'original_name' => 'corr.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 1024,
        ]);

        $response = $this->actingAs($owner)->get('/laporan/'.$correction->id.'/koreksi/edit');

        $response->assertOk();
        $response->assertViewHas('report', function (Report $report): bool {
            return $report->relationLoaded('originalReport')
                && $report->originalReport->relationLoaded('verification')
                && $report->relationLoaded('attachments');
        });
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);
        return User::factory()->create([
            'role_id' => $role->id,
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
        ]);
    }
}
