<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;

use App\Models\Report;
use App\Models\User;
use App\Services\ReportSubmissionService;
use App\Services\ReportTicketGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportCorrectionController extends Controller
{
    public function create(Request $request, Report $report, ReportTicketGenerator $ticketGenerator): RedirectResponse
    {
        abort_unless($report->user_id === $request->user()->id, 404);
        abort_unless($report->verification?->hasil === 'TIDAK_SESUAI', 422);

        $correction = $ticketGenerator->generate(
            $report->tanggal_kejadian,
            function (string $ticket) use ($request, $report): Report {
                $existing = Report::query()
                    ->where('koreksi_dari', $report->id)
                    ->first();

                if ($existing) {
                    return $existing;
                }

                return Report::create([
                    'user_id' => $request->user()->id,
                    'koreksi_dari' => $report->id,
                    'nomor_tiket' => $ticket,
                    'status' => 'DRAF',
                    'tanggal_kejadian' => $report->tanggal_kejadian,
                    'provinsi' => $report->provinsi,
                    'kabupaten' => $report->kabupaten,
                    'kecamatan' => $report->kecamatan,
                    'desa' => $report->desa,
                    'nama_saluran' => $report->nama_saluran,
                    'jenis_kerusakan' => $report->jenis_kerusakan,
                    'deskripsi' => $report->deskripsi,
                    'panjang_terdampak_m' => $report->panjang_terdampak_m,
                    'luas_terdampak_ha' => $report->luas_terdampak_ha,
                    'koordinat' => $report->koordinat,
                    'catatan' => 'Koreksi dari '.$report->nomor_tiket,
                    'sumber_isian' => 'MANUAL',
                ]);
            }
        );

        return redirect('/laporan/'.$correction->id.'/koreksi/edit');
    }

    public function edit(Request $request, Report $report): View
    {
        abort_unless($report->user_id === $request->user()->id && $report->koreksi_dari && $report->status === 'DRAF', 404);

        $report->load(['originalReport.verification', 'attachments']);

        return view('reports.correction-edit', ['report' => $report]);
    }

    public function submit(Request $request, Report $report, ReportSubmissionService $submissionService): RedirectResponse
    {
        abort_unless($report->user_id === $request->user()->id && $report->koreksi_dari, 404);
        abort_if($report->status !== 'DRAF', 422, 'Koreksi sudah terkirim.');
        abort_if($report->attachments()->count() < 1, 422, 'Minimal satu lampiran wajib ditambahkan sebelum dikirim.');

        $payload = $report->only([
            'tanggal_kejadian', 'provinsi', 'kabupaten', 'kecamatan', 'desa',
            'nama_saluran', 'jenis_kerusakan', 'deskripsi', 'panjang_terdampak_m',
            'luas_terdampak_ha', 'koordinat', 'catatan',
        ]);
        $this->validateFields($request->merge($payload));

        $submissionService->submit($request->user(), $report);

        return redirect('/laporan')->with('status', 'Koreksi '.$report->nomor_tiket.' berhasil dikirim.');
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        abort_unless($report->user_id === $request->user()->id && $report->koreksi_dari && $report->status === 'DRAF', 404);

        $validated = $this->validateFields($request);

        $report->update($validated);

        return redirect('/laporan/'.$report->id.'/koreksi/edit')->with('status', 'Draf koreksi berhasil diperbarui.');
    }

    private function validateFields(Request $request): array
    {
        return $request->validate([
            'tanggal_kejadian' => ['required', 'date', 'before_or_equal:today'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kabupaten' => ['required', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'desa' => ['nullable', 'string', 'max:100'],
            'nama_saluran' => ['nullable', 'string', 'max:150'],
            'jenis_kerusakan' => ['required', 'in:Saluran Primer,Saluran Sekunder,Saluran Tersier,Bendung,Bangunan Bagi,Pintu Air,Lainnya'],
            'deskripsi' => ['required', 'string', 'max:500'],
            'panjang_terdampak_m' => ['nullable', 'numeric', 'min:0'],
            'luas_terdampak_ha' => ['nullable', 'numeric', 'min:0'],
            'koordinat' => ['nullable', 'string', 'max:100'],
            'catatan' => ['nullable', 'string'],
        ]);
    }
}

// The correction remains a draft until the separate submit slice is implemented.
