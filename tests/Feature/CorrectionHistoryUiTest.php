<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorrectionHistoryUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_correction_detail_links_to_its_original_report(): void
    {
        [$owner, $original, $correction] = $this->reportPair();

        $this->actingAs($owner)
            ->get('/laporan/'.$correction->id)
            ->assertOk()
            ->assertSee('Koreksi dari')
            ->assertSee($original->nomor_tiket)
            ->assertSee('/laporan/'.$original->id);
    }

    public function test_original_detail_links_to_existing_correction(): void
    {
        [$owner, $original, $correction] = $this->reportPair();

        $this->actingAs($owner)
            ->get('/laporan/'.$original->id)
            ->assertOk()
            ->assertSee('Laporan koreksi')
            ->assertSee($correction->nomor_tiket)
            ->assertSee('/laporan/'.$correction->id);
    }

    private function reportPair(): array
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        $owner = User::factory()->create(['role_id' => $role->id]);
        $original = Report::create([
            'user_id' => $owner->id,
            'nomor_tiket' => 'LP-20261101-9101',
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan asal.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);
        $correction = Report::create([
            'user_id' => $owner->id,
            'koreksi_dari' => $original->id,
            'nomor_tiket' => 'LP-20261101-9102',
            'status' => 'DRAF',
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan koreksi.',
            'sumber_isian' => 'MANUAL',
        ]);

        return [$owner, $original, $correction];
    }
}
