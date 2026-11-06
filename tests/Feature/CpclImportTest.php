<?php

namespace Tests\Feature;

use App\Models\CpclImportBatch;
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
            ."1,DI Pamanukan,Kabupaten/Kota,Referensi,Jawa Barat,Subang,Pamanukan,Mulyasari,P3A Tirta,Sutrisno,1,25.0,107.1234,-6.1234\n";
        $file = UploadedFile::fake()->createWithContent('cpcl.csv', $csv);

        $preview = $this->actingAs($admin)->post('/admin/cpcl/import/preview', ['file' => $file]);
        $preview->assertOk()->assertSee('Baru valid')->assertSee('P3A Tirta');
        $this->assertDatabaseCount('cpcl_records', 0);

        $batch = CpclImportBatch::query()->firstOrFail();
        $this->post('/admin/cpcl/import/'.$batch->id.'/confirm')
            ->assertRedirect('/admin-only')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('cpcl_records', [
            'nama_di' => 'DI Pamanukan', 'kel_tani' => 'P3A Tirta', 'kabupaten' => 'Subang',
        ]);
    }

    public function test_import_preview_rejects_wrong_role(): void
    {
        $reporter = $this->userWithRole('pelapor_daerah');
        $file = UploadedFile::fake()->createWithContent('cpcl.csv', "No.,Nama DI\n1,DI\n");

        $this->actingAs($reporter)->post('/admin/cpcl/import/preview', ['file' => $file])->assertForbidden();
    }

    public function test_super_admin_can_import_xlsx_using_first_sheet_and_cached_values(): void
    {
        $admin = $this->userWithRole('super_admin');
        $zip = new \ZipArchive;
        $path = tempnam(sys_get_temp_dir(), 'cpcl-xlsx-');
        $zip->open($path, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/></Types>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="CPCL" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Target="worksheets/sheet1.xml" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"/></Relationships>');
        $zip->addFromString('xl/sharedStrings.xml', '<?xml version="1.0"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'.implode('', array_map(fn ($v) => '<si><t>'.htmlspecialchars($v, ENT_XML1).'</t></si>', ['Nama DI', 'Provinsi', 'Kab_Kota', 'Kecamatan', 'Desa/Kelurahan', 'Kel_Tani', 'DI XLSX', 'Jawa Barat', 'Subang', 'Pamanukan', 'Mulyasari', 'P3A XLSX'])).'</sst>');
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData><row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c><c r="C1" t="s"><v>2</v></c><c r="D1" t="s"><v>3</v></c><c r="E1" t="s"><v>4</v></c><c r="F1" t="s"><v>5</v></c></row><row r="2"><c r="A2" t="s"><v>6</v></c><c r="B2" t="s"><v>7</v></c><c r="C2" t="s"><v>8</v></c><c r="D2" t="s"><v>9</v></c><c r="E2" t="s"><v>10</v></c><c r="F2" t="s"><v>11</v></c></row></sheetData></worksheet>');
        $zip->close();
        $xlsx = file_get_contents($path);
        @unlink($path);

        $file = UploadedFile::fake()->createWithContent('cpcl.xlsx', $xlsx);
        $this->actingAs($admin)->post('/admin/cpcl/import/preview', ['file' => $file])
            ->assertOk()->assertSee('DI XLSX')->assertSee('Baru valid');
        $batch = CpclImportBatch::query()->latest()->firstOrFail();
        $this->post('/admin/cpcl/import/'.$batch->id.'/confirm')->assertRedirect('/admin-only');
        $this->assertDatabaseHas('cpcl_records', ['nama_di' => 'DI XLSX', 'kel_tani' => 'P3A XLSX']);
    }

    private function userWithRole(string $code): User
    {
        $role = Role::firstOrCreate(['code' => $code], ['name' => $code]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
