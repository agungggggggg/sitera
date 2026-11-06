<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class XlsxExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporter_can_download_own_native_xlsx(): void
    {
        $reporter = $this->userWithRole('pelapor_daerah');
        $own = $this->reportFor($reporter, 'LP-20261101-0001');
        $other = $this->reportFor($this->userWithRole('pelapor_daerah'), 'LP-20261101-0002');

        $response = $this->actingAs($reporter)->get('/ekspor?format=xlsx');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('content-disposition');
        $body = $response->getContent();
        $this->assertStringStartsWith("PK", $body);
        $temporary = tempnam(sys_get_temp_dir(), 'sitera-xlsx-test-');
        file_put_contents($temporary, $body);
        $archive = new \ZipArchive();
        $this->assertTrue($archive->open($temporary) === true);
        $sheet = $archive->getFromName('xl/worksheets/sheet1.xml');
        $archive->close();
        @unlink($temporary);
        $this->assertIsString($sheet);
        $this->assertNotFalse(simplexml_load_string($sheet));
        $this->assertStringContainsString($own->nomor_tiket, $sheet);
        $this->assertStringNotContainsString($other->nomor_tiket, $sheet);
    }

    private function reportFor(User $user, string $ticket): Report
    {
        return Report::create([
            'user_id' => $user->id,
            'nomor_tiket' => $ticket,
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => Carbon::yesterday(),
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan untuk ekspor Excel.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
