<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportDraftEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_edit_form_prefilled_with_draft_values(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'DRAF');

        $this->actingAs($owner)->get('/laporan/'.$report->id.'/edit')
            ->assertOk()
            ->assertSee('Edit draf laporan')
            ->assertSee('Deskripsi awal.', false)
            ->assertSee('Simpan perubahan');
    }

    public function test_owner_can_update_own_draft_report(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'DRAF');

        $this->actingAs($owner)->put('/laporan/'.$report->id, [
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Deskripsi sudah diperbaiki.',
        ])->assertRedirect('/laporan/'.$report->id);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'deskripsi' => 'Deskripsi sudah diperbaiki.',
            'status' => 'DRAF',
        ]);
    }

    public function test_user_cannot_update_another_owners_draft(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $other = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'DRAF');

        $this->actingAs($other)->put('/laporan/'.$report->id, [
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Tidak boleh mengubah.',
        ])->assertNotFound();
    }

    public function test_submitted_report_cannot_be_updated(): void
    {
        $owner = $this->userWithRole('pelapor_daerah');
        $report = $this->reportFor($owner, 'TERKIRIM');

        $this->actingAs($owner)->put('/laporan/'.$report->id, [
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Tidak boleh mengubah.',
        ])->assertNotFound();
    }

    private function reportFor(User $owner, string $status): Report
    {
        return Report::create([
            'user_id' => $owner->id,
            'nomor_tiket' => 'LP-20261101-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => $status,
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Deskripsi awal.',
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
