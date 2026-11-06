<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Notifications\SiteraEventNotification;
use App\Services\AuditLogger;
use App\Services\RegionCatalog;
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

    public function submit(Request $request, int $report): RedirectResponse
    {
        $reportModel = Report::query()
            ->whereKey($report)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_if($reportModel->status !== 'DRAF', 422, 'Laporan yang sudah terkirim tidak dapat dikirim ulang.');
        if ($reportModel->cpcl_id !== null) {
            $categories = $reportModel->attachments()->where('versi', $reportModel->versi)->pluck('kategori')->unique()->all();
            abort_unless(array_diff(['BAST', 'LPJ', 'DOKUMENTASI'], $categories) === [], 422, 'Lengkapi BAST, LPJ, dan dokumentasi sebelum mengirim laporan.');
        } else {
            abort_if($reportModel->attachments()->count() < 1, 422, 'Minimal satu lampiran wajib ditambahkan sebelum dikirim.');
        }

        $reportModel->forceFill([
            'status' => 'TERKIRIM',
            'dikirim_pada' => now(),
        ])->save();

        $isCorrection = $reportModel->koreksi_dari !== null;
        $verifiers = User::query()->whereHas('role', fn ($query) => $query->where('code', 'verifikator_pusat'))->get();
        foreach ($verifiers as $verifier) {
            $verifier->notify(new SiteraEventNotification(
                $isCorrection ? 'koreksi_dikirim' : 'laporan_baru',
                $isCorrection ? 'Koreksi laporan baru' : 'Laporan baru masuk',
                $reportModel->nomor_tiket.' telah dikirim dan menunggu pemeriksaan.',
                $reportModel->id,
                $reportModel->nomor_tiket,
            ));
        }

        app(AuditLogger::class)->log($request->user(), 'report.submitted', 'success', 'report', $reportModel->id);

        return redirect('/laporan')->with('status', 'Laporan '.$reportModel->nomor_tiket.' berhasil dikirim.');
    }

    public function store(Request $request, RegionCatalog $regions): RedirectResponse
    {
        $validated = $this->validatedReport($request, $regions);

        $report = DB::transaction(function () use ($validated, $request): Report {
            $date = Carbon::parse($validated['tanggal_kejadian']);
            $prefix = 'LP-'.$date->format('Ymd').'-';
            $last = Report::query()
                ->where('nomor_tiket', 'like', $prefix.'%')
                ->lockForUpdate()
                ->orderByDesc('nomor_tiket')
                ->value('nomor_tiket');
            $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

            return Report::create(array_merge($validated, [
                'user_id' => $request->user()->id,
                'nomor_tiket' => $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                'status' => 'DRAF',
                'sumber_isian' => 'MANUAL',
            ]));
        });

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
