<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CpclImportBatch;
use App\Models\CpclRecord;
use App\Services\AuditLogger;
use App\Services\CpclImportClassifier;
use App\Services\CpclSpreadsheetReader;
use App\Services\CpclTemplateXlsxExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class AdminCpclImportController extends Controller
{
    public function preview(Request $request, CpclSpreadsheetReader $reader, CpclImportClassifier $classifier): View|RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:csv,txt'],
        ], [
            'file.mimes' => 'Format impor CPCL hanya mendukung file CSV (.csv).',
        ]);

        try {
            $rows = $reader->read($validated['file']);
        } catch (RuntimeException $exception) {
            return redirect()->route('admin.index', ['view' => 'imports'])
                ->withErrors(['file' => $exception->getMessage()])
                ->withInput();
        }

        if (count($rows) > 5000) {
            return redirect()->route('admin.index', ['view' => 'imports'])
                ->withErrors(['file' => 'Maksimal 5.000 baris data per file.']);
        }

        [$classified, $summary] = $classifier->classify($rows);
        $batch = CpclImportBatch::create([
            'user_id' => $request->user()->id,
            'source_name' => mb_substr($validated['file']->getClientOriginalName(), 0, 255),
            'rows' => $classified,
            'summary' => $summary,
        ]);

        app(AuditLogger::class)->log($request->user(), 'cpcl.import_previewed', 'success', 'import_batch', $batch->id, ['count' => count($rows)]);

        return view('admin.cpcl-import-preview', compact('batch'));
    }

    public function confirm(Request $request, CpclImportBatch $batch, CpclImportClassifier $classifier): RedirectResponse
    {
        abort_unless($batch->user_id === $request->user()->id, 404);
        abort_if($batch->confirmed_at !== null, 409, 'Batch impor ini sudah diproses.');

        $result = DB::transaction(function () use ($batch, $request, $classifier): array {
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
                    if ($classifier->matchesRecord($existing, $row)) {
                        $skipped['Duplikat']++;
                    } else {
                        $skipped['Konflik']++;
                    }

                    continue;
                }

                CpclRecord::create($classifier->toModelData($row) + ['created_by' => $request->user()->id]);
                $added++;
            }

            $batch->forceFill(['confirmed_at' => now()])->save();

            return compact('added', 'skipped');
        });

        app(AuditLogger::class)->log($request->user(), 'cpcl.import_confirmed', 'success', 'import_batch', $batch->id, ['created_count' => $result['added'], 'skipped_count' => array_sum($result['skipped'])]);
        $summary = 'Impor CPCL selesai: '.$result['added'].' ditambahkan, '.$result['skipped']['Duplikat'].' duplikat, '.$result['skipped']['Konflik'].' konflik, '.$result['skipped']['Tidak valid'].' tidak valid dilewati. Data lama tidak ditimpa.';

        return redirect()->route('admin.index', ['view' => 'imports'])
            ->with('status', $summary)
            ->with('import_result', [
                'source_name' => $batch->source_name,
                'added' => $result['added'],
                'duplicate' => $result['skipped']['Duplikat'],
                'conflict' => $result['skipped']['Konflik'],
                'invalid' => $result['skipped']['Tidak valid'],
                'confirmed_at' => now()->translatedFormat('d M Y, H.i'),
            ]);
    }

    public function template(CpclTemplateXlsxExporter $exporter): Response
    {
        return response($exporter->export(), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template-impor-cpcl-sitera.xlsx"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    public function cancel(Request $request, CpclImportBatch $batch): RedirectResponse
    {
        abort_unless($batch->user_id === $request->user()->id, 404);
        if ($batch->confirmed_at === null) {
            $batch->delete();
            app(AuditLogger::class)->log($request->user(), 'cpcl.import_cancelled', 'success', 'import_batch', $batch->id);
        }

        return redirect()->route('admin.index', ['view' => 'imports'])
            ->with('status', 'Batch impor dibatalkan; data CPCL tidak diubah.');
    }
}
