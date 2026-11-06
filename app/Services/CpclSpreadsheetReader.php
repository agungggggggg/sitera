<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use ZipArchive;

class CpclSpreadsheetReader
{
    private const MAX_ROWS = 5000;
    private const MAX_EXPANDED_BYTES = 30_000_000;

    private const ALIASES = [
        'no' => ['no', 'nomor'],
        'nama_di' => ['namadi', 'namadaerahirigasi'],
        'kewenangan' => ['kewenangan'],
        'keterangan' => ['keterangan'],
        'provinsi' => ['provinsi'],
        'kabupaten' => ['kabkota', 'kabupatenkota', 'kabupaten', 'kota'],
        'kecamatan' => ['kecamatan'],
        'desa' => ['desakelurahan', 'desa', 'kelurahan'],
        'kel_tani' => ['keltani', 'kelompoktani'],
        'nama_ketua' => ['namaketua'],
        'realisasi' => ['realisasi'],
        'luas_ha' => ['luasha', 'luas'],
        'x' => ['x'],
        'y' => ['y'],
    ];

    public function read(UploadedFile $file): array
    {
        return match (strtolower($file->getClientOriginalExtension())) {
            'csv' => $this->readCsv($file->getRealPath()),
            'xlsx' => $this->readXlsx($file->getRealPath()),
            default => throw new RuntimeException('Format harus CSV atau XLSX.'),
        };
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('File CSV tidak dapat dibaca.');
        }

        try {
            $rows = [];
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = array_map(static fn ($value) => is_string($value) ? trim($value) : '', $row);
                if (count($rows) > self::MAX_ROWS + 1) {
                    throw new RuntimeException('Maksimal 5.000 baris data per file.');
                }
            }
        } finally {
            fclose($handle);
        }

        if (isset($rows[0][0])) {
            $rows[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $rows[0][0]);
        }

        return $this->mapRows($rows);
    }

    private function readXlsx(string $path): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi ZIP PHP tidak tersedia untuk membaca XLSX.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File XLSX tidak valid atau rusak.');
        }

        try {
            if ($zip->numFiles > 500 || $zip->locateName('xl/vbaProject.bin') !== false) {
                throw new RuntimeException('File XLSX dengan makro atau struktur berlebihan tidak diizinkan.');
            }

            $expanded = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $expanded += (int) ($stat['size'] ?? 0);
                if ($expanded > self::MAX_EXPANDED_BYTES) {
                    throw new RuntimeException('Ukuran konten XLSX setelah dibuka terlalu besar.');
                }
            }

            $workbook = $this->xml($zip, 'xl/workbook.xml');
            $relations = $this->xml($zip, 'xl/_rels/workbook.xml.rels');
            $workbookXpath = new DOMXPath($workbook);
            $workbookXpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $workbookXpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $sheet = $workbookXpath->query('//m:sheets/m:sheet')->item(0);
            if (!$sheet instanceof \DOMElement) {
                throw new RuntimeException('Workbook XLSX tidak memiliki sheet.');
            }
            $relationId = $sheet->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');

            $relationXpath = new DOMXPath($relations);
            $relationXpath->registerNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');
            $target = '';
            foreach ($relationXpath->query('//r:Relationship') as $relation) {
                if ($relation instanceof \DOMElement && $relation->getAttribute('Id') === $relationId) {
                    $target = $relation->getAttribute('Target');
                    break;
                }
            }
            if ($target === '' || str_contains($target, '..')) {
                throw new RuntimeException('Referensi sheet XLSX tidak valid.');
            }
            $sheetPath = str_starts_with($target, '/') ? ltrim($target, '/') : 'xl/'.ltrim($target, '/');
            $sheetXml = $this->xml($zip, $sheetPath);
            $strings = $this->sharedStrings($zip);
            $xpath = new DOMXPath($sheetXml);
            $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            $matrix = [];
            foreach ($xpath->query('//m:sheetData/m:row') as $row) {
                $cells = [];
                foreach ($xpath->query('./m:c', $row) as $cell) {
                    if (!$cell instanceof \DOMElement) {
                        continue;
                    }
                    if ($xpath->query('./m:f', $cell)->length > 0) {
                        throw new RuntimeException('Rumus Excel tidak diimpor. Salin hasil sebagai nilai terlebih dahulu.');
                    }
                    preg_match('/^([A-Z]+)/', $cell->getAttribute('r'), $match);
                    $columnIndex = isset($match[1]) ? $this->columnIndex($match[1]) : null;
                    if ($columnIndex === null) {
                        continue;
                    }
                    $type = $cell->getAttribute('t');
                    if ($type === 'inlineStr') {
                        $value = $xpath->query('./m:is//m:t', $cell);
                        $text = '';
                        foreach ($value as $part) $text .= $part->textContent;
                    } else {
                        $value = $xpath->query('./m:v', $cell)->item(0)?->textContent ?? '';
                        $text = $type === 's' ? ($strings[(int) $value] ?? '') : $value;
                    }
                    $cells[$columnIndex] = trim((string) $text);
                }
                if ($cells !== []) {
                    $max = max(array_keys($cells));
                    $matrix[] = array_map(static fn ($i) => $cells[$i] ?? '', range(0, $max));
                }
                if (count($matrix) > self::MAX_ROWS + 1) {
                    throw new RuntimeException('Maksimal 5.000 baris data per file.');
                }
            }

            return $this->mapRows($matrix);
        } finally {
            $zip->close();
        }
    }

    private function xml(ZipArchive $zip, string $name): DOMDocument
    {
        $contents = $zip->getFromName($name);
        if (!is_string($contents) || strlen($contents) > self::MAX_EXPANDED_BYTES) {
            throw new RuntimeException('Struktur XLSX tidak lengkap atau terlalu besar.');
        }

        $document = new DOMDocument();
        $document->resolveExternals = false;
        $document->substituteEntities = false;
        if (!$document->loadXML($contents, LIBXML_NONET | LIBXML_NOBLANKS | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            throw new RuntimeException('XML dalam XLSX tidak valid.');
        }
        return $document;
    }

    private function sharedStrings(ZipArchive $zip): array
    {
        $contents = $zip->getFromName('xl/sharedStrings.xml');
        if (!is_string($contents)) return [];

        $document = new DOMDocument();
        $document->resolveExternals = false;
        $document->substituteEntities = false;
        if (!$document->loadXML($contents, LIBXML_NONET | LIBXML_NOBLANKS | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            throw new RuntimeException('Shared strings XLSX tidak valid.');
        }
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $strings = [];
        foreach ($xpath->query('//m:si') as $item) {
            $text = '';
            foreach ($xpath->query('.//m:t', $item) as $part) $text .= $part->textContent;
            $strings[] = $text;
        }
        return $strings;
    }

    private function mapRows(array $matrix): array
    {
        if (count($matrix) < 2) {
            throw new RuntimeException('File harus berisi satu baris header dan minimal satu baris data.');
        }
        $headerIndex = null;
        $mapping = [];
        foreach (array_slice($matrix, 0, 10, true) as $index => $row) {
            $candidate = [];
            foreach ($row as $column => $label) {
                $normalized = preg_replace('/[^a-z0-9]/', '', strtolower((string) $label));
                foreach (self::ALIASES as $field => $aliases) {
                    if (in_array($normalized, $aliases, true)) $candidate[$field] = $column;
                }
            }
            if (isset($candidate['nama_di'], $candidate['provinsi'], $candidate['kabupaten'], $candidate['kel_tani'])) {
                $headerIndex = $index;
                $mapping = $candidate;
                break;
            }
        }
        if ($headerIndex === null) {
            throw new RuntimeException('Header CPCL tidak ditemukan. Gunakan template dan pastikan kolom Nama DI, Provinsi, Kab_Kota, dan Kel_Tani tersedia.');
        }

        $rows = [];
        foreach (array_slice($matrix, $headerIndex + 1, null, true) as $rowIndex => $row) {
            $record = [];
            foreach (self::ALIASES as $field => $_) {
                $record[$field] = isset($mapping[$field]) ? trim((string) ($row[$mapping[$field]] ?? '')) : '';
            }
            if (count(array_filter($record, static fn ($value) => $value !== '')) === 0) continue;
            $record['_row'] = $rowIndex + 1;
            $rows[] = $record;
        }
        return $rows;
    }

    private function columnIndex(string $letters): int
    {
        $index = 0;
        foreach (str_split($letters) as $letter) $index = $index * 26 + (ord($letter) - 64);
        return $index - 1;
    }
}

