<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportVerification;
use App\Notifications\SiteraEventNotification;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportVerificationController extends Controller
{
    public function store(Request $request, Report $report): RedirectResponse
    {
        abort_unless($report->status === 'TERKIRIM', 404);

        $validated = $request->validate([
            'hasil' => ['required', Rule::in(['SESUAI', 'TIDAK_SESUAI'])],
            'catatan' => [
                Rule::requiredIf(fn () => $request->input('hasil') === 'TIDAK_SESUAI'),
                'nullable', 'string', 'max:2000',
            ],
        ]);

        $verification = DB::transaction(function () use ($report, $validated, $request): ReportVerification {
            $lockedReport = Report::query()->lockForUpdate()->findOrFail($report->id);
            abort_unless($lockedReport->status === 'TERKIRIM', 404);
            abort_if($lockedReport->verification()->exists(), 422, 'Laporan sudah diperiksa.');

            return $lockedReport->verification()->create([
                'verifier_id' => $request->user()->id,
                'hasil' => $validated['hasil'],
                'catatan' => $validated['catatan'] ?? null,
                'diperiksa_pada' => now(),
            ]);
        });

        $report->user->notify(new SiteraEventNotification(
            'hasil_verifikasi',
            'Hasil pemeriksaan tersedia',
            $report->nomor_tiket.' diperiksa: '.$validated['hasil'].'. '.($validated['catatan'] ?? ''),
            $report->id,
            $report->nomor_tiket,
            $validated['hasil'],
        ));

        app(AuditLogger::class)->log($request->user(), 'report.verified', 'success', 'report', $report->id);

        return redirect()->route('verification.show', $report)->with('status', 'Hasil pemeriksaan berhasil disimpan.');
    }
}
