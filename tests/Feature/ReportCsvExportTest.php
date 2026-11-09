<?php

namespace Tests\Feature;

use App\Http\Controllers\Exports\ExportController;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use App\Services\ReportCsvExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportCsvExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_headers_and_column_order_match_specification(): void
    {
        $exporter = app(ReportCsvExporter::class);
        $this->assertSame([
            'Nomor Tiket',
            'Status',
            'Tanggal Kejadian',
            'Pelapor',
            'Provinsi',
            'Kabupaten',
            'Jenis Kerusakan',
            'Deskripsi',
            'Dikirim Pada',
        ], ReportCsvExporter::HEADERS);

        $csv = $exporter->export(collect([]));
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);

        // Strip BOM and inspect header row
        $withoutBom = substr($csv, 3);
        $rows = array_filter(explode("\n", str_replace("\r", '', $withoutBom)));
        $firstRow = str_getcsv($rows[0]);
        $this->assertSame(ReportCsvExporter::HEADERS, $firstRow);
    }

    public function test_csv_serializes_complete_report_and_handles_null_values(): void
    {
        $reporter = $this->userWithRole('pelapor_daerah', 'Budi Santoso');
        $reportComplete = Report::create([
            'user_id' => $reporter->id,
            'nomor_tiket' => 'LP-20261101-0001',
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => '2026-10-25',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Kerusakan dinding penahan tanah.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => '2026-11-01 10:30:00',
        ]);

        $reportWithNulls = Report::create([
            'user_id' => $reporter->id,
            'nomor_tiket' => 'LP-20261101-0002',
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => '2026-10-26',
            'provinsi' => 'Jawa Tengah',
            'kabupaten' => 'Klaten',
            'jenis_kerusakan' => 'Pintu Air',
            'deskripsi' => 'Tanpa dikirim_pada.',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => null,
        ]);

        $exporter = app(ReportCsvExporter::class);
        $csv = $exporter->export(collect([$reportComplete, $reportWithNulls]));

        $rows = array_map('str_getcsv', array_filter(explode("\n", str_replace("\r", '', substr($csv, 3)))));
        $this->assertCount(3, $rows); // 1 header + 2 data rows

        $row1 = $rows[1];
        $this->assertSame('LP-20261101-0001', $row1[0]);
        $this->assertSame('TERKIRIM', $row1[1]);
        $this->assertSame('2026-10-25', $row1[2]);
        $this->assertSame('Budi Santoso', $row1[3]);
        $this->assertSame('Jawa Barat', $row1[4]);
        $this->assertSame('Subang', $row1[5]);
        $this->assertSame('Saluran Sekunder', $row1[6]);
        $this->assertSame('Kerusakan dinding penahan tanah.', $row1[7]);
        $this->assertSame('2026-11-01 10:30:00', $row1[8]);

        $row2 = $rows[2];
        $this->assertSame('LP-20261101-0002', $row2[0]);
        $this->assertSame('TERKIRIM', $row2[1]);
        $this->assertSame('2026-10-26', $row2[2]);
        $this->assertSame('Budi Santoso', $row2[3]);
        $this->assertSame('Jawa Tengah', $row2[4]);
        $this->assertSame('Klaten', $row2[5]);
        $this->assertSame('Pintu Air', $row2[6]);
        $this->assertSame('Tanpa dikirim_pada.', $row2[7]);
        $this->assertSame('', $row2[8]); // null dikirim_pada formatted as empty string
    }

    public function test_csv_properly_escapes_commas_quotes_and_newlines_in_text(): void
    {
        $reporter = $this->userWithRole('pelapor_daerah', 'Ahmad "Pelapor" Syarif');
        $complexDescription = "Saluran jebol di KM 12,5;\nMemerlukan \"bronjong kawat\" dan batu kali segera,\nvolume 15 m3.";

        $report = Report::create([
            'user_id' => $reporter->id,
            'nomor_tiket' => 'LP-20261101-0003',
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => '2026-10-28',
            'provinsi' => 'Jawa Timur',
            'kabupaten' => 'Pasuruan',
            'jenis_kerusakan' => 'Bendung',
            'deskripsi' => $complexDescription,
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => '2026-11-02 08:15:00',
        ]);

        $exporter = app(ReportCsvExporter::class);
        $csv = $exporter->export(collect([$report]));

        // Parse with PHP native fgetcsv on memory stream to prove standard CSV parser compatibility
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, substr($csv, 3)); // skip BOM
        rewind($stream);

        $parsedHeader = fgetcsv($stream);
        $parsedRow = fgetcsv($stream);
        fclose($stream);

        $this->assertSame(ReportCsvExporter::HEADERS, $parsedHeader);
        $this->assertSame('Ahmad "Pelapor" Syarif', $parsedRow[3]);
        $this->assertSame($complexDescription, $parsedRow[7]);
    }

    public function test_csv_download_response_headers_disposition_and_filename(): void
    {
        $executive = $this->userWithRole('pimpinan_kementan');
        $report = Report::create([
            'user_id' => $executive->id,
            'nomor_tiket' => 'LP-20261101-0004',
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => '2026-10-29',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan uji header',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);

        $response = $this->actingAs($executive)->get('/ekspor?format=csv');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertNotNull($disposition);
        $this->assertStringStartsWith('attachment; filename=', $disposition);
        $this->assertMatchesRegularExpression('/sitera-rekap-\d{8}-\d{6}\.csv/', $disposition);

        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('LP-20261101-0004', $content);
    }

    public function test_executive_can_export_csv_with_region_and_date_filters(): void
    {
        $executive = $this->userWithRole('pimpinan_kementan');
        $pelapor = $this->userWithRole('pelapor_daerah');

        $match = Report::create([
            'user_id' => $pelapor->id,
            'nomor_tiket' => 'LP-20261101-0010',
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => '2026-11-05',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Cocok filter',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => Carbon::parse('2026-11-05 10:00:00'),
        ]);

        $differentRegion = Report::create([
            'user_id' => $pelapor->id,
            'nomor_tiket' => 'LP-20261101-0011',
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => '2026-11-05',
            'provinsi' => 'Jawa Tengah',
            'kabupaten' => 'Banyumas',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Beda wilayah',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => Carbon::parse('2026-11-05 10:00:00'),
        ]);

        $response = $this->actingAs($executive)->get('/ekspor?format=csv&provinsi=Jawa+Barat&kabupaten=Subang&dari=2026-11-01&sampai=2026-11-10');

        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString($match->nomor_tiket, $csv);
        $this->assertStringNotContainsString($differentRegion->nomor_tiket, $csv);
    }

    public function test_legacy_csv_method_continues_to_function_via_exporter_service(): void
    {
        $executive = $this->userWithRole('pimpinan_kementan');
        $report = Report::create([
            'user_id' => $executive->id,
            'nomor_tiket' => 'LP-20261101-0020',
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => '2026-10-30',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Legacy method report',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);

        $controller = app(ExportController::class);
        $request = Request::create('/ekspor', 'GET', ['format' => 'csv']);
        $request->setUserResolver(fn () => $executive);

        $response = $controller->csv($request);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('LP-20261101-0020', $content);
    }

    private function userWithRole(string $code, ?string $name = null): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => str_replace('_', ' ', $code)]);
        return User::factory()->create([
            'role_id' => $role->id,
            'name' => $name ?? 'User ' . $code,
        ]);
    }
}
