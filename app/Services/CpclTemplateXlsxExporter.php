<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

/**
 * Generates the styled Excel (.xlsx) CPCL import template.
 */
class CpclTemplateXlsxExporter
{
    private const TEMPLATE_ROW_COUNT = 100;

    private const FIRST_DATA_ROW = 3;

    /**
     * @var list<array{column: string, header: string, width: int, styleIndex: int}>
     */
    private const COLUMNS = [
        ['column' => 'A', 'header' => 'No.', 'width' => 7, 'styleIndex' => 5],
        ['column' => 'B', 'header' => 'Nama DI', 'width' => 24, 'styleIndex' => 6],
        ['column' => 'C', 'header' => 'Kewenangan', 'width' => 18, 'styleIndex' => 6],
        ['column' => 'D', 'header' => 'Keterangan', 'width' => 18, 'styleIndex' => 7],
        ['column' => 'E', 'header' => 'Provinsi', 'width' => 18, 'styleIndex' => 5],
        ['column' => 'F', 'header' => 'Kab_Kota', 'width' => 20, 'styleIndex' => 5],
        ['column' => 'G', 'header' => 'Kecamatan', 'width' => 20, 'styleIndex' => 5],
        ['column' => 'H', 'header' => 'Desa/Kelurahan', 'width' => 22, 'styleIndex' => 5],
        ['column' => 'I', 'header' => 'Kel_Tani', 'width' => 22, 'styleIndex' => 5],
        ['column' => 'J', 'header' => 'Nama Ketua', 'width' => 20, 'styleIndex' => 6],
        ['column' => 'K', 'header' => 'Realisasi', 'width' => 15, 'styleIndex' => 8],
        ['column' => 'L', 'header' => 'Luas_ha', 'width' => 15, 'styleIndex' => 5],
        ['column' => 'M', 'header' => 'X', 'width' => 15, 'styleIndex' => 5],
        ['column' => 'N', 'header' => 'Y', 'width' => 15, 'styleIndex' => 5],
    ];

    /**
     * Build the binary XLSX contents for the CPCL import template.
     */
    public function export(): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'sitera_cpcl_tpl_');
        if ($tmpPath === false) {
            throw new RuntimeException('Unable to allocate temporary file for CPCL template export.');
        }

        $zip = new ZipArchive;
        if ($zip->open($tmpPath, ZipArchive::OVERWRITE) !== true) {
            @unlink($tmpPath);
            throw new RuntimeException('Unable to open ZIP archive for CPCL template export.');
        }

        try {
            $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
            $zip->addFromString('_rels/.rels', $this->rootRelationshipsXml());
            $zip->addFromString('xl/workbook.xml', $this->workbookXml());
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
            $zip->addFromString('xl/styles.xml', $this->stylesXml());
            $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml());
            $zip->close();

            $binary = file_get_contents($tmpPath);
            if ($binary === false) {
                throw new RuntimeException('Unable to read generated CPCL template archive.');
            }

            return $binary;
        } finally {
            @unlink($tmpPath);
        }
    }

    private function contentTypesXml(): string
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

    private function rootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Template_Kosong" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function workbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="5">'
            .'<font><sz val="11"/><color rgb="FF1E293B"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="12"/><color rgb="FF1F4E78"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><color rgb="FF375623"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><color rgb="FF000000"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="10">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFE5E9EC"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFE2EFDA"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFA9D08E"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF4B084"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF548235"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFFCE4D6"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF4F7F4"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="4">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border>'
            .'<left style="thin"><color rgb="FF7F7F7F"/></left>'
            .'<right style="thin"><color rgb="FF7F7F7F"/></right>'
            .'<top style="thin"><color rgb="FF7F7F7F"/></top>'
            .'<bottom style="thin"><color rgb="FF7F7F7F"/></bottom>'
            .'<diagonal/>'
            .'</border>'
            .'<border>'
            .'<left style="thin"><color rgb="FFD9DFD9"/></left>'
            .'<right style="thin"><color rgb="FFD9DFD9"/></right>'
            .'<top style="thin"><color rgb="FFD9DFD9"/></top>'
            .'<bottom style="thin"><color rgb="FFD9DFD9"/></bottom>'
            .'<diagonal/>'
            .'</border>'
            .'<border><left/><right/><top/><bottom style="thin"><color rgb="FF7F7F7F"/></bottom><diagonal/></border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="11">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="3" fillId="2" borderId="3" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="4" fontId="3" fillId="3" borderId="3" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="3" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="3" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="4" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="3" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="9" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="8" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    private function worksheetXml(): string
    {
        $lastDataRow = self::FIRST_DATA_ROW + self::TEMPLATE_ROW_COUNT - 1;

        $colsXml = '<cols>';
        foreach (self::COLUMNS as $index => $definition) {
            $colNumber = $index + 1;
            $colsXml .= '<col min="'.$colNumber.'" max="'.$colNumber.'" width="'.$definition['width'].'" customWidth="1"/>';
        }
        $colsXml .= '</cols>';

        $rowsXml = $this->titleRowXml($lastDataRow).$this->headerRowXml();
        for ($row = self::FIRST_DATA_ROW; $row <= $lastDataRow; $row++) {
            $styleIndex = $row % 2 === 1 ? 9 : 10;
            $rowsXml .= $this->blankDataRowXml($row, $styleIndex);
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<dimension ref="A1:N'.$lastDataRow.'"/>'
            .'<sheetViews><sheetView tabSelected="1" workbookViewId="0">'
            .'<pane ySplit="2" topLeftCell="A3" activePane="bottomLeft" state="frozen"/>'
            .'</sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="19"/>'
            .$colsXml
            .'<sheetData>'.$rowsXml.'</sheetData>'
            .'<autoFilter ref="A2:N'.$lastDataRow.'"/>'
            .'</worksheet>';
    }

    private function titleRowXml(int $lastDataRow): string
    {
        return '<row r="1" ht="22" customHeight="1">'
            .$this->inlineStringCellXml('A1', 'TEMPLATE DATA DAERAH IRIGASI (SIAP ISI)', 1)
            .$this->inlineStringCellXml('J1', 'TOTAL:', 2)
            .'<c r="K1" s="3"><f>COUNTA(B'.self::FIRST_DATA_ROW.':B'.$lastDataRow.')</f><v>0</v></c>'
            .'<c r="L1" s="4"><f>SUM(L'.self::FIRST_DATA_ROW.':L'.$lastDataRow.')</f><v>0</v></c>'
            .'</row>';
    }

    private function headerRowXml(): string
    {
        $cellsXml = '';
        foreach (self::COLUMNS as $definition) {
            $cellsXml .= $this->inlineStringCellXml(
                $definition['column'].'2',
                $definition['header'],
                $definition['styleIndex']
            );
        }

        return '<row r="2" ht="24" customHeight="1">'.$cellsXml.'</row>';
    }

    private function blankDataRowXml(int $rowNumber, int $styleIndex): string
    {
        $cellsXml = '';
        foreach (self::COLUMNS as $definition) {
            $cellsXml .= '<c r="'.$definition['column'].$rowNumber.'" s="'.$styleIndex.'"/>';
        }

        return '<row r="'.$rowNumber.'" ht="19" customHeight="1">'.$cellsXml.'</row>';
    }

    private function inlineStringCellXml(string $cellRef, string $value, int $styleIndex): string
    {
        $escaped = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return '<c r="'.$cellRef.'" s="'.$styleIndex.'" t="inlineStr"><is><t>'.$escaped.'</t></is></c>';
    }
}
