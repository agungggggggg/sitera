<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportAttachmentPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_owner_can_preview_an_image_attachment(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('reports/image.png', 'png-content');
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'DRAF');
        $attachment = $report->attachments()->create([
            'path' => 'reports/image.png',
            'original_name' => 'evidence.png',
            'mime_type' => 'image/png',
            'size_bytes' => 11,
        ]);

        $this->actingAs($owner)->get('/lampiran/'.$attachment->id.'/preview')
            ->assertOk()
            ->assertHeader('content-type', 'image/png')
            ->assertHeader('content-disposition', 'inline; filename=evidence.png');
    }

    public function test_other_user_cannot_preview_attachment(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('reports/image.png', 'png-content');
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'TERKIRIM');
        $attachment = $report->attachments()->create([
            'path' => 'reports/image.png',
            'original_name' => 'evidence.png',
            'mime_type' => 'image/png',
            'size_bytes' => 11,
        ]);

        $this->actingAs($this->userWithRole('pelapor_daerah'))
            ->get('/lampiran/'.$attachment->id.'/preview')
            ->assertNotFound();
    }

    public function test_unsupported_file_type_cannot_be_previewed(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('reports/data.csv', 'some,data');
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'DRAF');
        $attachment = $report->attachments()->create([
            'path' => 'reports/data.csv',
            'original_name' => 'data.csv',
            'mime_type' => 'text/csv',
            'size_bytes' => 9,
        ]);

        $this->actingAs($owner)->get('/lampiran/'.$attachment->id.'/preview')->assertNotFound();
    }

    public function test_verifier_can_preview_submitted_pdf_inside_same_origin_iframe(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('reports/bast.pdf', '%PDF-1.4 test');
        $owner = $this->userWithRole('pelapor_daerah');
        $verifier = $this->userWithRole('verifikator_pusat');
        $report = $this->reportFor($owner, 'TERKIRIM');
        $attachment = $report->attachments()->create([
            'kategori' => 'BAST',
            'versi' => 1,
            'path' => 'reports/bast.pdf',
            'original_name' => 'BAST-contoh.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 13,
        ]);

        $response = $this->actingAs($verifier)->get('/lampiran/'.$attachment->id.'/preview');
        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename=BAST-contoh.pdf');
        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringNotContainsString('sandbox', $csp);
    }

    private function reportFor(User $owner, string $status): Report
    {
        return Report::create([
            'user_id' => $owner->id,
            'nomor_tiket' => 'LP-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => $status,
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan uji preview.',
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
