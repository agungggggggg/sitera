<?php

namespace App\Http\Controllers;

use App\Models\CpclImportBatch;
use App\Models\CpclRecord;
use App\Services\AuditLogger;
use App\Services\CpclSpreadsheetReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class AdminCpclImportController extends Controller
{
    private const FIELDS = ['no', 'nama_di', 'kewenangan', 'keterangan', 'provinsi', 'kabupaten', 'kecamatan', 'desa', 'kel_tani', 'nama_ketua', 'realisasi', 'luas_ha', 'x', 'y'];

    public function preview(Request $request, CpclSpreadsheetReader $reader): View|RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:csv,xlsx'],
        ]);

        try {
            $rows = $reader->read($validated['file']);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()])->withInput();
        }

        if (count($rows) > 5000) {
            return back()->withErrors(['file' => 'Maksimal 5.000 baris data per file.']);
        }

        [$classified, $summary] = $this->classify($rows);
        $batch = CpclImportBatch::create([
            'user_id' => $request->user()->id,
            'source_name' => mb_substr($validated['file']->getClientOriginalName(), 0, 255),
            'rows' => $classified,
            'summary' => $summary,
        ]);

        app(AuditLogger::class)->log($request->user(), 'cpcl.import_previewed', 'success', 'import_batch', $batch->id, ['count' => count($rows)]);

        return view('admin.cpcl-import-preview', compact('batch'));
    }

    public function confirm(Request $request, CpclImportBatch $batch): RedirectResponse
    {
        abort_unless($batch->user_id === $request->user()->id, 404);
        abort_if($batch->confirmed_at !== null, 409, 'Batch impor ini sudah diproses.');

        $result = DB::transaction(function () use ($batch, $request): array {
            $added = 0;
            $skipped = ['Duplikat' => 0, 'Konflik' => 0, 'Tidak valid' => 0];

            foreach ($batch->rows as $row) {
                if (($row['_status'] ?? '') !== 'Baru valid') {
                    $skipped[$row['_status']] = ($skipped[$row['_status']] ?? 0) + 1;

                    continue;
                }

                $identityQuery = CpclRecord::query()->where('provinsi', $row['provinsi'])
                    ->where('kabupaten', $row['kabupaten'])->where('kecamatan', $row['kecamatan'])
                    ->where('desa', $row['desa'])->where('kel_tani', $row['kel_tani'])
                    ->lockForUpdate();
                $existing = $identityQuery->first();
                if ($existing) {
                    $candidate = $this->toModelData($row);
                    $existingData = $existing->only(self::FIELDS);
                    if ($this->sameContent($existingData, $candidate)) {
                        $skipped['Duplikat']++;
                    } else {
                        $skipped['Konflik']++;
                    }

                    continue;
                }

                CpclRecord::create($this->toModelData($row) + ['created_by' => $request->user()->id]);
                $added++;
            }

            $batch->forceFill(['confirmed_at' => now()])->save();

            return compact('added', 'skipped');
        });

        app(AuditLogger::class)->log($request->user(), 'cpcl.import_confirmed', 'success', 'import_batch', $batch->id, ['created_count' => $result['added'], 'skipped_count' => array_sum($result['skipped'])]);
        $summary = 'Impor CPCL selesai: '.$result['added'].' ditambahkan, '.$result['skipped']['Duplikat'].' duplikat, '.$result['skipped']['Konflik'].' konflik, '.$result['skipped']['Tidak valid'].' tidak valid dilewati. Data lama tidak ditimpa.';

        return redirect('/admin-only')->with('status', $summary);
    }

    public function template()
    {
        $headers = ['No.', 'Nama DI', 'Kewenangan', 'Keterangan', 'Provinsi', 'Kab_Kota', 'Kecamatan', 'Desa/Kelurahan', 'Kel_Tani', 'Nama Ketua', 'Realisasi', 'Luas_ha', 'X', 'Y'];

        return response()->streamDownload(static function () use ($headers): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, '\\xEF\\xBB\\xBF');
            fputcsv($output, $headers);
            fclose($output);
        }, 'template-impor-cpcl-sitera.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function cancel(Request $request, CpclImportBatch $batch): RedirectResponse
    {
        abort_unless($batch->user_id === $request->user()->id, 404);
        if ($batch->confirmed_at === null) {
            $batch->delete();
            app(AuditLogger::class)->log($request->user(), 'cpcl.import_cancelled', 'success', 'import_batch', $batch->id);
        }

        return redirect('/admin-only')->with('status', 'Batch impor dibatalkan; data CPCL tidak diubah.');
    }

    private function classify(array $rows): array
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
            $identity = $this->identity($row);
            $groups[$identity][] = $index;
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

    private function identity(array $row): string
    {
        return implode('|', array_map(static fn ($key) => mb_strtolower(trim((string) $row[$key])), ['provinsi', 'kabupaten', 'kecamatan', 'desa', 'kel_tani']));
    }

    private function toModelData(array $row): array
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
}
