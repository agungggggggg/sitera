<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;

use App\Models\Report;
use App\Models\User;
use App\Services\RegionCatalog;
use App\Services\ReportSubmissionService;
use App\Services\ReportTicketGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function create(RegionCatalog $regions): View
    {
        return view('reports.create', [
            'report' => null,
            'provinces' => $regions->provinceNames(),
            'regencies' => [],
            'regionCatalog' => $regions->all(),
        ]);
    }

    public function edit(Request $request, int $report, RegionCatalog $regions): View
    {
        $draft = Report::query()
            ->whereKey($report)
            ->where('user_id', $request->user()->id)
            ->where('status', 'DRAF')
            ->whereNull('koreksi_dari')
            ->firstOrFail();

        return view('reports.create', [
            'report' => $draft,
            'provinces' => $regions->provinceNames(),
            'regencies' => $regions->regenciesFor($draft->provinsi),
            'regionCatalog' => $regions->all(),
        ]);
    }

    public function update(Request $request, int $report, RegionCatalog $regions): RedirectResponse
    {
        $draft = Report::query()
            ->whereKey($report)
            ->where('user_id', $request->user()->id)
            ->where('status', 'DRAF')
            ->whereNull('koreksi_dari')
            ->firstOrFail();

        $draft->update($this->validatedReport($request, $regions));

        return redirect()->route('reports.show', $draft)->with('status', 'Draf '.$draft->nomor_tiket.' berhasil diperbarui.');
    }

    public function submit(Request $request, int $report, ReportSubmissionService $submissionService): RedirectResponse
    {
        $reportModel = Report::query()
            ->whereKey($report)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $submissionService->submit($request->user(), $reportModel);

        return redirect('/laporan')->with('status', 'Laporan '.$reportModel->nomor_tiket.' berhasil dikirim.');
    }

    public function store(Request $request, RegionCatalog $regions, ReportTicketGenerator $ticketGenerator): RedirectResponse
    {
        $validated = $this->validatedReport($request, $regions);

        $report = $ticketGenerator->generate(
            $validated['tanggal_kejadian'],
            fn (string $ticket): Report => Report::create(array_merge($validated, [
                'user_id' => $request->user()->id,
                'nomor_tiket' => $ticket,
                'status' => 'DRAF',
                'sumber_isian' => 'MANUAL',
            ]))
        );

        return redirect('/laporan')->with('status', 'Draf laporan '.$report->nomor_tiket.' berhasil dibuat.');
    }

    private function validatedReport(Request $request, RegionCatalog $regions): array
    {
        $province = (string) $request->input('provinsi', '');
        $regencies = array_column($regions->regenciesFor($province), 'value');

        return $request->validate([
            'tanggal_kejadian' => ['required', 'date', 'before_or_equal:today'],
            'provinsi' => ['required', 'string', Rule::in($regions->provinceNames())],
            'kabupaten' => ['required', 'string', Rule::in($regencies)],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'desa' => ['nullable', 'string', 'max:100'],
            'nama_saluran' => ['nullable', 'string', 'max:150'],
            'jenis_kerusakan' => ['required', Rule::in(['Saluran Primer', 'Saluran Sekunder', 'Saluran Tersier', 'Bendung', 'Bangunan Bagi', 'Pintu Air', 'Lainnya'])],
            'deskripsi' => ['required', 'string', 'max:500'],
            'panjang_terdampak_m' => ['nullable', 'numeric', 'min:0'],
            'luas_terdampak_ha' => ['nullable', 'numeric', 'min:0'],
            'koordinat' => ['nullable', 'string', 'max:100'],
            'catatan' => ['nullable', 'string'],
        ]);
    }
}
