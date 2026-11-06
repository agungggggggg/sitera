<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_upload_an_attachment_to_a_draft(): void
    {
        Storage::fake('local');
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->draftFor($owner, 'LP-20261101-0001');
        $file = UploadedFile::fake()->create('bukti.jpg', 120, 'image/jpeg');

        $this->actingAs($owner)
            ->post('/laporan/'.$report->id.'/lampiran', ['file' => $file])
            ->assertRedirect('/laporan/'.$report->id);

        $this->assertDatabaseHas('report_attachments', [
            'report_id' => $report->id,
            'original_name' => 'bukti.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        Storage::disk('local')->assertExists((string) \DB::table('report_attachments')->value('path'));
    }

    public function test_attachment_is_required_before_submitting_a_normal_report(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->draftFor($owner, 'LP-20261101-0002');

        $this->actingAs($owner)
            ->post('/laporan/'.$report->id.'/kirim')
            ->assertStatus(422);

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'DRAF']);
    }

    public function test_attachment_is_required_before_submitting_a_correction(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $original = $this->draftFor($owner, 'LP-20261101-0003');
        $correction = $this->draftFor($owner, 'LP-20261101-0004', $original->id);

        $this->actingAs($owner)
            ->post('/laporan/'.$correction->id.'/koreksi/kirim')
            ->assertStatus(422);

        $this->assertDatabaseHas('reports', ['id' => $correction->id, 'status' => 'DRAF']);
    }

    public function test_other_user_cannot_upload_to_a_report(): void
    {
        Storage::fake('local');
        $owner = $this->userWithRole('pelapor_daerah');
        $other = User::factory()->create(['role_id' => Role::where('code', 'pelapor_daerah')->value('id')]);
        $report = $this->draftFor($owner, 'LP-20261101-0005');

        $this->actingAs($other)
            ->post('/laporan/'.$report->id.'/lampiran', ['file' => UploadedFile::fake()->create('bukti.jpg', 120, 'image/jpeg')])
            ->assertNotFound();
    }

    private function draftFor(User $user, string $ticket, ?int $correctionFrom = null): Report
    {
        return Report::create([
            'user_id' => $user->id,
            'koreksi_dari' => $correctionFrom,
            'nomor_tiket' => $ticket,
            'status' => 'DRAF',
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan dengan data lengkap.',
            'sumber_isian' => 'MANUAL',
        ]);
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);
        return User::factory()->create(['role_id' => $role->id]);
    }
}
