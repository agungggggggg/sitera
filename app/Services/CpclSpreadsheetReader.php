<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class CpclSpreadsheetReader
{
    private const MAX_ROWS = 5000;

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

    /**
     * Read and map rows from an uploaded CPCL CSV file.
     *
     * @return array<int, array<string, mixed>>
     */
    public function read(UploadedFile $file): array
    {
        if (strtolower($file->getClientOriginalExtension()) !== 'csv') {
            throw new RuntimeException('Format impor CPCL hanya mendukung file CSV (.csv).');
        }

        return $this->readCsv($file->getRealPath());
    }

    /**
     * Parse CSV rows using auto-detected comma or semicolon delimiter.
     *
     * @return array<int, array<string, mixed>>
     */
    private function readCsv(string $path): array
    {
        $delimiter = $this->detectDelimiter($path);
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('File CSV tidak dapat dibaca.');
        }

        try {
            $rows = [];
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rows[] = array_map(static fn ($value) => is_string($value) ? trim($value) : '', $row);
                if (count($rows) > self::MAX_ROWS + 10) {
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

    private function detectDelimiter(string $path): string
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return ',';
        }

        $commaScore = 0;
        $semicolonScore = 0;

        try {
            $checked = 0;
            while ($checked < 10 && ($line = fgets($handle)) !== false) {
                $trimmed = trim($line);
                if ($trimmed === '') {
                    continue;
                }
                $unquoted = preg_replace('/"[^"]*"/', '', $trimmed) ?? $trimmed;
                $commaScore += substr_count($unquoted, ',');
                $semicolonScore += substr_count($unquoted, ';');
                $checked++;
            }
        } finally {
            fclose($handle);
        }

        return $semicolonScore > $commaScore ? ';' : ',';
    }

    /**
     * Map raw CSV matrix rows into normalized CPCL fields.
     *
     * @param  array<int, array<int, string>>  $matrix
     * @return array<int, array<string, mixed>>
     */
    private function mapRows(array $matrix): array
    {
        if (count($matrix) < 2) {
            throw new RuntimeException('File CSV harus berisi satu baris header dan minimal satu baris data.');
        }

        $headerIndex = null;
        $mapping = [];
        foreach (array_slice($matrix, 0, 10, true) as $index => $row) {
            $candidate = $this->matchHeaderRow($row);
            if ($candidate !== null) {
                $headerIndex = $index;
                $mapping = $candidate;
                break;
            }
        }

        if ($headerIndex === null) {
            throw new RuntimeException('Header CPCL tidak ditemukan. Gunakan template CSV SITERA dan pastikan kolom Nama DI, Provinsi, Kab_Kota, dan Kel_Tani tersedia.');
        }

        $rows = [];
        foreach (array_slice($matrix, $headerIndex + 1, null, true) as $rowIndex => $row) {
            if ($this->matchHeaderRow($row) !== null) {
                continue;
            }

            $record = [];
            foreach (self::ALIASES as $field => $_) {
                $record[$field] = isset($mapping[$field]) ? trim((string) ($row[$mapping[$field]] ?? '')) : '';
            }
            if (count(array_filter($record, static fn ($value) => $value !== '')) === 0) {
                continue;
            }
            $record['_row'] = $rowIndex + 1;
            $rows[] = $record;
            if (count($rows) > self::MAX_ROWS) {
                throw new RuntimeException('Maksimal 5.000 baris data per file.');
            }
        }

        if ($rows === []) {
            throw new RuntimeException('File CSV tidak memiliki baris data di bawah header.');
        }

        return $rows;
    }

    /**
     * Match a candidate header row against known CPCL column aliases.
     *
     * @param  array<int, string>  $row
     * @return array<string, int>|null
     */
    private function matchHeaderRow(array $row): ?array
    {
        $candidate = [];
        foreach ($row as $column => $label) {
            $normalized = preg_replace('/[^a-z0-9]/', '', strtolower((string) $label));
            foreach (self::ALIASES as $field => $aliases) {
                if (in_array($normalized, $aliases, true)) {
                    $candidate[$field] = $column;
                }
            }
        }

        if (isset($candidate['nama_di'], $candidate['provinsi'], $candidate['kabupaten'], $candidate['kel_tani'])) {
            return $candidate;
        }

        return null;
    }
}
