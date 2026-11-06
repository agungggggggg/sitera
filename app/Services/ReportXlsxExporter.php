<?php

namespace App\Services;

use Illuminate\Support\Collection;
use RuntimeException;
use ZipArchive;

class ReportXlsxExporter
{
    public function export(Collection $reports): string
    {
        $temporary = tempnam(sys_get_temp_dir(), 'sitera-xlsx-');
        if ($temporary === false) {
            throw new RuntimeException('Tidak dapat membuat file sementara ekspor.');
        }

        $zip = new ZipArchive();
        if ($zip->open($temporary, ZipArchive::OVERWRITE) !== true) {
            @unlink($temporary);
            throw new RuntimeException('Tidak dapat menyusun berkas Excel.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRelationships());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationships());
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheet($reports));
        $zip->close();

        $content = file_get_contents($temporary);
        @unlink($temporary);
        if (! is_string($content)) {
            throw new RuntimeException('Tidak dapat membaca berkas Excel sementara.');
        }

        return $content;
    }

    private function worksheet(Collection $reports): string
    {
        $headers = ['Nomor Tiket', 'Status', 'Tanggal Kejadian', 'Pelapor', 'Provinsi', 'Kabupaten', 'Jenis Kerusakan', 'Deskripsi', 'Dikirim Pada'];
        $xmlRows = [];
        $xmlRows[] = $this->row(1, $headers, true);
        $row = 2;
        foreach ($reports as $report) {
            $xmlRows[] = $this->row($row++, [
                $report->nomor_tiket,
                $report->status,
                $report->tanggal_kejadian?->format('Y-m-d'),
                $report->user?->name,
                $report->provinsi,
                $report->kabupaten,
                $report->jenis_kerusakan,
                $report->deskripsi,
                $report->dikirim_pada?->format('Y-m-d H:i:s'),
            ]);
        }

        $lastRow = max(1, $row - 1);
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="18"/><cols><col min="1" max="1" width="22" customWidth="1"/><col min="2" max="9" width="20" customWidth="1"/></cols>'
            .'<sheetData>'.implode('', $xmlRows).'</sheetData><autoFilter ref="A1:I'.$lastRow.'"/></worksheet>';
    }

    private function row(int $number, array $values, bool $header = false): string
    {
        $cells = '';
        foreach (array_values($values) as $index => $value) {
            $column = $this->columnName($index + 1);
            $style = $header ? ' s="1"' : '';
            $cells .= '<c r="'.$column.$number.'" t="inlineStr"'.$style.'><is><t xml:space="preserve">'.$this->escape((string) ($value ?? '')).'</t></is></c>';
        }
        return '<row r="'.$number.'">'.$cells.'</row>';
    }

    private function columnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)).$name;
            $index = intdiv($index, 26);
        }
        return $name;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private function rootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Rekap Laporan" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function workbookRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF123B52"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }
}
