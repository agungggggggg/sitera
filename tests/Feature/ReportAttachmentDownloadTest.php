<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportAttachment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportAttachmentDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_verifier_can_download_attachment_for_submitted_report(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('reports/1/evidence.pdf', '%PDF-test');
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'TERKIRIM');
        $attachment = $report->attachments()->create([
            'path' => 'reports/1/evidence.pdf',
            'original_name' => 'evidence.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 9,
        ]);
        $verifier = $this->userWithRole('verifikator_pusat');

        $this->actingAs($verifier)
            ->get('/lampiran/'.$attachment->id)
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_unrelated_user_cannot_download_attachment(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('reports/1/evidence.pdf', '%PDF-test');
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'TERKIRIM');
        $attachment = $report->attachments()->create([
            'path' => 'reports/1/evidence.pdf',
            'original_name' => 'evidence.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 9,
        ]);
        $unrelated = $this->userWithRole('pelapor_daerah');

        $this->actingAs($unrelated)->get('/lampiran/'.$attachment->id)->assertNotFound();
    }

    public function test_verifier_cannot_download_attachment_from_draft_report(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('reports/1/evidence.pdf', '%PDF-test');
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'DRAF');
        $attachment = $report->attachments()->create([
            'path' => 'reports/1/evidence.pdf',
            'original_name' => 'evidence.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 9,
        ]);
        $verifier = $this->userWithRole('verifikator_pusat');

        $this->actingAs($verifier)->get('/lampiran/'.$attachment->id)->assertNotFound();
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
            'deskripsi' => 'Laporan pengujian lampiran.',
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
