<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_submit_a_draft_report(): void
    {
        $user = $this->userWithRole('pelapor_daerah');
        $report = $this->draftFor($user);

        $response = $this->actingAs($user)->post('/laporan/'.$report->id.'/kirim');

        $response->assertRedirect('/laporan');
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'TERKIRIM',
        ]);
        $this->assertNotNull($report->fresh()->dikirim_pada);
    }

    public function test_user_cannot_submit_another_users_report(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $other = $this->userWithRole('pelapor_daerah');
        $report = $this->draftFor($owner);

        $this->actingAs($other)
            ->post('/laporan/'.$report->id.'/kirim')
            ->assertNotFound();

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'DRAF',
            'dikirim_pada' => null,
        ]);
    }

    public function test_submitted_report_cannot_be_submitted_again(): void
    {
        $user = $this->userWithRole('pelapor_daerah');
        $report = $this->draftFor($user);
        $report->update(['status' => 'TERKIRIM', 'dikirim_pada' => now()]);

        $this->actingAs($user)
            ->post('/laporan/'.$report->id.'/kirim')
            ->assertStatus(422);
    }

    private function draftFor(User $user): Report
    {
        $report = Report::create([
            'user_id' => $user->id,
            'nomor_tiket' => 'LP-'.Carbon::today()->format('Ymd').'-0001-'.$user->id,
            'status' => 'DRAF',
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Kerusakan saluran untuk pengujian.',
            'sumber_isian' => 'MANUAL',
        ]);
        $report->attachments()->create([
            'path' => 'tests/attachment.jpg',
            'original_name' => 'attachment.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 100,
        ]);

        return $report;
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => $code]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
