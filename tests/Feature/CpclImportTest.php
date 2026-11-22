<?php

namespace Tests\Feature;

use App\Models\CpclImportBatch;
use App\Models\CpclRecord;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CpclImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_preview_and_confirm_new_csv_cpcl_rows(): void
    {
        $admin = $this->userWithRole('super_admin');
        $csv = "No.,Nama DI,Kewenangan,Keterangan,Provinsi,Kab_Kota,Kecamatan,Desa/Kelurahan,Kel_Tani,Nama Ketua,Realisasi,Luas_ha,X,Y\n"
            . "1,DI Pamanukan,Kabupaten/Kota,Referensi,Jawa Barat,Subang,Pamanukan,Mulyasari,P3A Tirta,Sutrisno,1,25.0,107.1234,-6.1234\n";
        $file = UploadedFile::fake()->createWithContent('cpcl.csv', $csv);

        $preview = $this->actingAs($admin)->post('/admin/cpcl/import/preview', ['file' => $file]);
        $preview->assertOk()->assertSee('Baru valid')->assertSee('P3A Tirta');
        $this->assertDatabaseCount('cpcl_records', 0);

        $batch = CpclImportBatch::query()->firstOrFail();
        $confirm = $this->post('/admin/cpcl/import/' . $batch->id . '/confirm');
        $confirm->assertRedirect(route('admin.index', ['view' => 'imports']))
            ->assertSessionHas('status')
            ->assertSessionHas('import_result');

        $this->followRedirects($confirm)
            ->assertOk()
            ->assertSee('Ditambahkan')
            ->assertSee('Lihat audit log')
            ->assertSee('Impor file lain');

        $this->assertDatabaseHas('cpcl_records', [
            'nama_di' => 'DI Pamanukan',
            'kel_tani' => 'P3A Tirta',
            'kabupaten' => 'Subang',
        ]);
    }

    public function test_import_preview_rejects_wrong_role(): void
    {
        $reporter = $this->userWithRole('pelapor_daerah');
        $file = UploadedFile::fake()->createWithContent('cpcl.csv', "No.,Nama DI\n1,DI\n");

        $this->actingAs($reporter)->post('/admin/cpcl/import/preview', ['file' => $file])->assertForbidden();
    }

    public function test_import_preview_rejects_xlsx_and_xls_files(): void
    {
        $admin = $this->userWithRole('super_admin');
        $xlsxFile = UploadedFile::fake()->create('cpcl.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $xlsFile = UploadedFile::fake()->create('cpcl.xls', 10, 'application/vnd.ms-excel');

        $this->actingAs($admin)->post('/admin/cpcl/import/preview', ['file' => $xlsxFile])
            ->assertSessionHasErrors('file');
        $this->actingAs($admin)->post('/admin/cpcl/import/preview', ['file' => $xlsFile])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('cpcl_import_batches', 0);
        $this->assertDatabaseCount('cpcl_records', 0);
    }

    public function test_template_xlsx_matches_styled_spreadsheet_and_csv_export_imports_cleanly(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)->get('/admin/cpcl/template.csv')
            ->assertRedirect('/admin/cpcl/template.xlsx');

        $response = $this->actingAs($admin)->get(route('admin.cpcl.import.template'));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('content-disposition', 'attachment; filename="template-impor-cpcl-sitera.xlsx"');

        $tmpPath = tempnam(sys_get_temp_dir(), 'cpcl_tpl_test_');
        $this->assertNotFalse($tmpPath);
        file_put_contents($tmpPath, $response->getContent());

        try {
            $zip = new \ZipArchive;
            $this->assertTrue($zip->open($tmpPath) === true);

            $workbookXml = $zip->getFromName('xl/workbook.xml');
            $stylesXml = $zip->getFromName('xl/styles.xml');
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            $zip->close();

            $this->assertIsString($workbookXml);
            $this->assertIsString($stylesXml);
            $this->assertIsString($sheetXml);
            $this->assertStringContainsString('name="Template_Kosong"', $workbookXml);
            $this->assertStringContainsString('TEMPLATE DATA DAERAH IRIGASI (SIAP ISI)', $sheetXml);
            $this->assertStringContainsString('TOTAL:', $sheetXml);
            $this->assertStringContainsString('COUNTA(B3:B102)', $sheetXml);
            $this->assertStringContainsString('SUM(L3:L102)', $sheetXml);
            $this->assertStringContainsString('ySplit="2"', $sheetXml);
            $this->assertStringContainsString('<autoFilter ref="A2:N102"/>', $sheetXml);

            $dom = new \DOMDocument;
            $this->assertTrue($dom->loadXML($sheetXml));
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            $headerTexts = [];
            foreach ($xpath->query('//m:row[@r="2"]/m:c/m:is/m:t') as $node) {
                $headerTexts[] = $node->textContent;
            }
            $this->assertSame([
                'No.',
                'Nama DI',
                'Kewenangan',
                'Keterangan',
                'Provinsi',
                'Kab_Kota',
                'Kecamatan',
                'Desa/Kelurahan',
                'Kel_Tani',
                'Nama Ketua',
                'Realisasi',
                'Luas_ha',
                'X',
                'Y',
            ], $headerTexts);

            $this->assertStringContainsString('FFA9D08E', $stylesXml);
            $this->assertStringContainsString('FFF4B084', $stylesXml);
            $this->assertStringContainsString('FF548235', $stylesXml);
            $this->assertStringContainsString('FFFCE4D6', $stylesXml);
        } finally {
            @unlink($tmpPath);
        }

        $uploadedTemplate = "TEMPLATE DATA DAERAH IRIGASI (SIAP ISI),,,,,,,,,TOTAL:,,,,\n"
            . "No.,Nama DI,Kewenangan,Keterangan,Provinsi,Kab_Kota,Kecamatan,Desa/Kelurahan,Kel_Tani,Nama Ketua,Realisasi,Luas_ha,X,Y\n"
            . "1,D.I. Way Gatel,Kabupaten/Kota,Pusat-P1,Lampung,Pringsewu,Gadingrejo,Wates,P3A Tirta Bersama,Arifin,1,25,105.0044,-5.3749\n";
        $file = UploadedFile::fake()->createWithContent('template-contoh.csv', "\xEF\xBB\xBF" . $uploadedTemplate);

        $this->actingAs($admin)->post('/admin/cpcl/import/preview', ['file' => $file])
            ->assertOk()
            ->assertSee('D.I. Way Gatel')
            ->assertSee('P3A Tirta Bersama');

        $batch = CpclImportBatch::query()->latest()->firstOrFail();
        $this->assertSame(['Baru valid' => 1, 'Duplikat' => 0, 'Konflik' => 0, 'Tidak valid' => 0], $batch->summary);

        $this->post('/admin/cpcl/import/' . $batch->id . '/confirm')
            ->assertRedirect(route('admin.index', ['view' => 'imports']));
        $this->assertDatabaseHas('cpcl_records', [
            'nama_di' => 'D.I. Way Gatel',
            'provinsi' => 'Lampung',
            'kabupaten' => 'Pringsewu',
            'kel_tani' => 'P3A Tirta Bersama',
        ]);
    }

    public function test_cancel_import_deletes_batch_and_redirects_to_imports_view(): void
    {
        $admin = $this->userWithRole('super_admin');
        $csv = "No.,Nama DI,Kewenangan,Keterangan,Provinsi,Kab_Kota,Kecamatan,Desa/Kelurahan,Kel_Tani,Nama Ketua,Realisasi,Luas_ha,X,Y\n"
            . "1,DI Batal,Kabupaten/Kota,Referensi,Jawa Barat,Subang,Pamanukan,Mulyasari,P3A Batal,Sutrisno,1,25.0,107.1234,-6.1234\n";
        $file = UploadedFile::fake()->createWithContent('cpcl.csv', $csv);

        $this->actingAs($admin)->post('/admin/cpcl/import/preview', ['file' => $file])->assertOk();
        $batch = CpclImportBatch::query()->firstOrFail();

        $this->post('/admin/cpcl/import/' . $batch->id . '/cancel')
            ->assertRedirect(route('admin.index', ['view' => 'imports']))
            ->assertSessionHas('status');

        $this->assertDatabaseCount('cpcl_import_batches', 0);
        $this->assertDatabaseCount('cpcl_records', 0);
    }

    public function test_preview_classifies_existing_duplicate_conflict_repeated_and_invalid_rows(): void
    {
        $admin = $this->userWithRole('super_admin');
        CpclRecord::create([
            'no' => 'CPCL-1',
            'nama_di' => 'DI Existing',
            'kewenangan' => 'Kabupaten/Kota',
            'keterangan' => 'Referensi',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan',
            'desa' => 'Mulyasari',
            'kel_tani' => 'P3A Existing',
            'nama_ketua' => 'Sutrisno',
            'realisasi' => '1',
            'luas_ha' => 25.0,
            'x' => 107.1234,
            'y' => -6.1234,
        ]);
        CpclRecord::create([
            'no' => 'CPCL-2',
            'nama_di' => 'DI Conflict Original',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan',
            'desa' => 'Sukamandi',
            'kel_tani' => 'P3A Conflict',
        ]);
        $csv = "No.,Nama DI,Kewenangan,Keterangan,Provinsi,Kab_Kota,Kecamatan,Desa/Kelurahan,Kel_Tani,Nama Ketua,Realisasi,Luas_ha,X,Y\n"
            . "CPCL-1,DI Existing,Kabupaten/Kota,Referensi,Jawa Barat,Subang,Pamanukan,Mulyasari,P3A Existing,Sutrisno,1,25.0,107.1234,-6.1234\n"
            . "CPCL-2,DI Changed,Kabupaten/Kota,Referensi,Jawa Barat,Subang,Pamanukan,Sukamandi,P3A Conflict,Sutrisno,1,25.0,107.1234,-6.1234\n"
            . "CPCL-3,DI New,Kabupaten/Kota,Referensi,Jawa Barat,Subang,Pamanukan,Legonkulon,P3A New,Siti,2,10.0,107.1,-6.1\n"
            . "CPCL-3,DI New,Kabupaten/Kota,Referensi,Jawa Barat,Subang,Pamanukan,Legonkulon,P3A New,Siti,2,10.0,107.1,-6.1\n"
            . "CPCL-4,DI Conflict First,Kabupaten/Kota,Referensi,Jawa Barat,Subang,Pamanukan,Desa Konflik,P3A Conflict In File,Siti,2,10.0,107.1,-6.1\n"
            . "CPCL-4,DI Conflict Second,Kabupaten/Kota,Referensi,Jawa Barat,Subang,Pamanukan,Desa Konflik,P3A Conflict In File,Siti,2,10.0,107.1,-6.1\n"
            . "5,,,,,,,,,,,,,\n";
        $file = UploadedFile::fake()->createWithContent('cpcl-matrix.csv', $csv);

        $this->actingAs($admin)->post('/admin/cpcl/import/preview', ['file' => $file])->assertOk();

        $batch = CpclImportBatch::query()->firstOrFail();
        $this->assertSame([
            'Baru valid' => 1,
            'Duplikat' => 2,
            'Konflik' => 3,
            'Tidak valid' => 1,
        ], $batch->summary);
        $this->assertSame(['Duplikat', 'Konflik', 'Baru valid', 'Duplikat', 'Konflik', 'Konflik', 'Tidak valid'], array_column($batch->rows, '_status'));

        $this->post('/admin/cpcl/import/' . $batch->id . '/confirm')->assertRedirect(route('admin.index', ['view' => 'imports']));
        $this->assertDatabaseCount('cpcl_records', 3);
        $this->assertDatabaseHas('cpcl_records', ['nama_di' => 'DI Existing', 'kel_tani' => 'P3A Existing']);
        $this->assertDatabaseHas('cpcl_records', ['nama_di' => 'DI New', 'kel_tani' => 'P3A New']);
        $this->assertDatabaseHas('cpcl_records', ['nama_di' => 'DI Conflict Original', 'kel_tani' => 'P3A Conflict']);
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => $code]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
