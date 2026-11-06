<?php

namespace App\Services;

use App\Models\CpclRecord;

class CpclImportClassifier
{
    private const FIELDS = ['no', 'nama_di', 'kewenangan', 'keterangan', 'provinsi', 'kabupaten', 'kecamatan', 'desa', 'kel_tani', 'nama_ketua', 'realisasi', 'luas_ha', 'x', 'y'];

    /**
     * Classify parsed CPCL rows for the import preview.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{0: array<int, array<string, mixed>>, 1: array<string, int>}
     */
    public function classify(array $rows): array
    {
        $classified = [];
        foreach ($rows as $row) {
            $missing = array_values(array_filter(['nama_di', 'provinsi', 'kabupaten', 'kecamatan', 'desa', 'kel_tani'], fn ($key) => trim($row[$key] ?? '') === ''));
            $invalid = null;
            foreach (['luas_ha', 'x', 'y'] as $numeric) {
                if (($row[$numeric] ?? '') !== '' && ! is_numeric($row[$numeric])) {
                    $invalid = $numeric.' harus berupa angka.';
                }
            }
            if ($invalid || $missing) {
                $row['_status'] = 'Tidak valid';
                $row['_reason'] = $invalid ?? 'Kolom wajib kosong: '.implode(', ', $missing).'.';
            } else {
                $row['_status'] = 'Baru valid';
                $row['_reason'] = 'Siap ditambahkan setelah konfirmasi.';
            }
            $classified[] = $row;
        }

        $groups = [];
        foreach ($classified as $index => $row) {
            if ($row['_status'] !== 'Baru valid') {
                continue;
            }
            $groups[$this->identity($row)][] = $index;
        }

        foreach ($groups as $indices) {
            $candidate = $classified[$indices[0]];
            $existing = CpclRecord::query()->where('provinsi', $candidate['provinsi'])
                ->where('kabupaten', $candidate['kabupaten'])->where('kecamatan', $candidate['kecamatan'])
                ->where('desa', $candidate['desa'])->where('kel_tani', $candidate['kel_tani'])->first();

            if ($existing) {
                $same = $this->sameContent($existing->only(self::FIELDS), $this->toModelData($candidate));
                foreach ($indices as $index) {
                    $classified[$index]['_status'] = $same ? 'Duplikat' : 'Konflik';
                    $classified[$index]['_reason'] = $same ? 'Identitas dan isi sama dengan data yang sudah ada.' : 'Identitas CPCL sudah ada dengan isi berbeda; data lama tidak ditimpa.';
                }

                continue;
            }

            $different = false;
            foreach (array_slice($indices, 1) as $index) {
                if (! $this->sameContent($this->toModelData($candidate), $this->toModelData($classified[$index]))) {
                    $different = true;
                }
            }
            if ($different) {
                foreach ($indices as $index) {
                    $classified[$index]['_status'] = 'Konflik';
                    $classified[$index]['_reason'] = 'Identitas yang sama muncul lebih dari sekali dengan isi berbeda; semua baris ditahan.';
                }
            } else {
                foreach (array_slice($indices, 1) as $index) {
                    $classified[$index]['_status'] = 'Duplikat';
                    $classified[$index]['_reason'] = 'Baris identik berulang dalam file; hanya satu baris yang akan ditambahkan.';
                }
            }
        }

        $summary = ['Baru valid' => 0, 'Duplikat' => 0, 'Konflik' => 0, 'Tidak valid' => 0];
        foreach ($classified as $row) {
            $summary[$row['_status']]++;
        }

        return [$classified, $summary];
    }

    /**
     * Normalize a preview row to the fields accepted by the CPCL model.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function toModelData(array $row): array
    {
        $data = [];
        foreach (self::FIELDS as $field) {
            $value = trim((string) ($row[$field] ?? ''));
            $data[$field] = $value === '' ? null : $value;
        }
        foreach (['luas_ha', 'x', 'y'] as $number) {
            $data[$number] = $data[$number] === null ? null : (float) str_replace(',', '.', $data[$number]);
        }

        return $data;
    }

    /**
     * Determine whether a stored CPCL record has the same importable content as a row.
     *
     * @param  array<string, mixed>  $row
     */
    public function matchesRecord(CpclRecord $record, array $row): bool
    {
        return $this->sameContent($record->only(self::FIELDS), $this->toModelData($row));
    }

    /**
     * Compare normalized importable values.
     *
     * @param  array<string, mixed>  $left
     * @param  array<string, mixed>  $right
     */
    private function sameContent(array $left, array $right): bool
    {
        foreach (self::FIELDS as $field) {
            $a = trim((string) ($left[$field] ?? ''));
            $b = trim((string) ($right[$field] ?? ''));
            if (in_array($field, ['luas_ha', 'x', 'y'], true) && $a !== '' && $b !== '' && is_numeric($a) && is_numeric($b)) {
                if ((float) $a !== (float) $b) {
                    return false;
                }
            } elseif (mb_strtolower($a) !== mb_strtolower($b)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Build the case-insensitive identity key used to group duplicate rows.
     *
     * @param  array<string, mixed>  $row
     */
    private function identity(array $row): string
    {
        return implode('|', array_map(static fn ($key) => mb_strtolower(trim((string) $row[$key])), ['provinsi', 'kabupaten', 'kecamatan', 'desa', 'kel_tani']));
    }
}
