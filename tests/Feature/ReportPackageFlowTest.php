<?php

namespace Tests\Feature;

use App\Models\CpclRecord;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportPackageFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporter_can_create_cpcl_package_and_upload_three_required_document_categories(): void
    {
        Storage::fake('local');
        $reporter = $this->reporter();
        $cpcl = $this->cpcl('Jawa Barat', 'Subang');

        $this->actingAs($reporter)->post(route('reports.package.store', $cpcl), [
            '_action' => 'draft',
            'bast' => UploadedFile::fake()->createWithContent('bast.pdf', "%PDF-1.4\nBukti BAST"),
            'lpj' => UploadedFile::fake()->createWithContent('lpj.pdf', "%PDF-1.4\nBukti LPJ"),
            'dokumentasi' => UploadedFile::fake()->create('dokumentasi.pptx', 10, 'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
        ])->assertRedirect('/laporan');

        $report = Report::query()->where('user_id', $reporter->id)->firstOrFail();
        $this->assertSame($cpcl->id, $report->cpcl_id);
        $this->assertDatabaseCount('report_attachments', 3);
        $this->assertDatabaseHas('report_attachments', ['report_id' => $report->id, 'kategori' => 'BAST']);
        $this->assertDatabaseHas('report_attachments', ['report_id' => $report->id, 'kategori' => 'LPJ']);
        $this->assertDatabaseHas('report_attachments', ['report_id' => $report->id, 'kategori' => 'DOKUMENTASI']);
    }

    public function test_reporter_cannot_select_cpcl_outside_assigned_region(): void
    {
        $reporter = $this->reporter();
        $otherRegionCpcl = $this->cpcl('Jawa Barat', 'Indramayu');

        $this->actingAs($reporter)->get(route('reports.package.create', $otherRegionCpcl))->assertNotFound();
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_draft_package_can_be_saved_without_all_documents_but_cannot_be_submitted(): void
    {
        $reporter = $this->reporter();
        $cpcl = $this->cpcl('Jawa Barat', 'Subang');

        $this->actingAs($reporter)->post(route('reports.package.store', $cpcl), [
            '_action' => 'draft',
            'bast' => UploadedFile::fake()->createWithContent('bast.pdf', "%PDF-1.4\nBAST"),
        ])->assertRedirect('/laporan');

        $report = Report::query()->where('user_id', $reporter->id)->firstOrFail();
        $this->actingAs($reporter)->post(route('reports.submit', $report))->assertStatus(422);
        $this->assertSame('DRAF', $report->fresh()->status);
    }

    public function test_reporter_can_submit_complete_package_directly_from_initial_cpcl_form(): void
    {
        Storage::fake('local');
        $reporter = $this->reporter();
        $cpcl = $this->cpcl('Jawa Barat', 'Subang');

        $this->actingAs($reporter)->post(route('reports.package.store', $cpcl), [
            '_action' => 'submit',
            'bast' => UploadedFile::fake()->createWithContent('bast.pdf', "%PDF-1.4\nBukti BAST"),
            'lpj' => UploadedFile::fake()->createWithContent('lpj.pdf', "%PDF-1.4\nBukti LPJ"),
            'dokumentasi' => UploadedFile::fake()->create('dokumentasi.pptx', 10, 'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
        ])->assertRedirect('/laporan');

        $report = Report::query()->where('user_id', $reporter->id)->firstOrFail();
        $this->assertSame('TERKIRIM', $report->fresh()->status);
        $this->assertNotNull($report->dikirim_pada);
    }

    private function reporter(): User
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);

        return User::factory()->create(['role_id' => $role->id, 'provinsi' => 'Jawa Barat', 'kabupaten' => 'Subang']);
    }

    private function cpcl(string $province, string $regency): CpclRecord
    {
        return CpclRecord::create([
            'nama_di' => 'DI Pamanukan', 'provinsi' => $province, 'kabupaten' => $regency,
            'kecamatan' => 'Pamanukan', 'desa' => 'Mulyasari', 'kel_tani' => 'P3A Tirta',
        ]);
    }
}
