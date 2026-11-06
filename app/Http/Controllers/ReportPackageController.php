<?php

namespace App\Http\Controllers;

use App\Models\CpclRecord;
use App\Models\Report;
use App\Models\ReportAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportPackageController extends Controller
{
    public function create(Request $request, CpclRecord $cpcl): View|RedirectResponse
    {
        $this->assertCpclRegion($request, $cpcl);
        $existing = Report::query()->where('user_id', $request->user()->id)->where('cpcl_id', $cpcl->id)->first();
        if ($existing) {
            return $existing->status === 'DRAF'
                ? redirect()->route('reports.package.edit', $existing)
                : redirect()->route('reports.show', $existing)->with('status', 'Paket laporan untuk CPCL ini sudah dikirim.');
        }

        return view('reports.package-form', ['cpcl' => $cpcl, 'report' => null]);
    }

    public function store(Request $request, CpclRecord $cpcl): RedirectResponse
    {
        $this->assertCpclRegion($request, $cpcl);
        $existing = Report::query()->where('user_id', $request->user()->id)->where('cpcl_id', $cpcl->id)->first();
        if ($existing) {
            return redirect()->route($existing->status === 'DRAF' ? 'reports.package.edit' : 'reports.show', $existing);
        }

        $action = $this->requestedAction($request);
        $files = $this->validatedFiles($request, $action === 'submit');
        $report = DB::transaction(function () use ($request, $cpcl): Report {
            $date = Carbon::now('Asia/Jakarta');
            $prefix = 'LP-'.$date->format('Ymd').'-';
            $last = Report::query()->where('nomor_tiket', 'like', $prefix.'%')->lockForUpdate()->orderByDesc('nomor_tiket')->value('nomor_tiket');
            $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

            return Report::create([
                'user_id' => $request->user()->id,
                'cpcl_id' => $cpcl->id,
                'nomor_tiket' => $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                'status' => 'DRAF',
                'versi' => 1,
                'tanggal_kejadian' => $date->toDateString(),
                'provinsi' => $cpcl->provinsi,
                'kabupaten' => $cpcl->kabupaten,
                'kecamatan' => $cpcl->kecamatan,
                'desa' => $cpcl->desa,
                'nama_saluran' => $cpcl->nama_di,
                'jenis_kerusakan' => 'Paket CPCL',
                'deskripsi' => 'Paket dokumen pelaporan CPCL '.$cpcl->kel_tani,
                'sumber_isian' => 'MANUAL',
            ]);
        });

        $this->storeAttachments($report, $files);

        if ($action === 'submit') {
            return app(ReportController::class)->submit($request, $report->id);
        }

        return redirect('/laporan')->with('status', 'Draf paket '.$report->nomor_tiket.' berhasil disimpan.');
    }

    public function edit(Request $request, Report $report): View
    {
        $this->assertEditable($request, $report);

        return view('reports.package-form', [
            'cpcl' => $report->cpclRecord()->firstOrFail(),
            'report' => $report->load('attachments'),
        ]);
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        $this->assertEditable($request, $report);
        $action = $this->requestedAction($request);
        $files = $this->validatedFiles($request, $action === 'submit', $report);
        $this->storeAttachments($report, $files);

        if ($action === 'submit') {
            return app(ReportController::class)->submit($request, $report->id);
        }

        return redirect()->route('reports.package.edit', $report)->with('status', 'Draf paket '.$report->nomor_tiket.' berhasil diperbarui.');
    }

    private function requestedAction(Request $request): string
    {
        return $request->validate(['_action' => ['required', 'in:draft,submit']])['_action'];
    }

    private function validatedFiles(Request $request, bool $required, ?Report $report = null): array
    {
        $existing = [];
        foreach (['bast' => 'BAST', 'lpj' => 'LPJ', 'dokumentasi' => 'DOKUMENTASI'] as $input => $category) {
            $existing[$input] = $report?->attachments()->where('versi', $report->versi)->where('kategori', $category)->exists() ?? false;
        }

        return $request->validate([
            'bast' => [$required && ! $existing['bast'] ? 'required' : 'nullable', 'file', 'max:10240', 'mimes:pdf'],
            'lpj' => [$required && ! $existing['lpj'] ? 'required' : 'nullable', 'file', 'max:10240', 'mimes:pdf'],
            'dokumentasi' => [$required && ! $existing['dokumentasi'] ? 'required' : 'nullable', 'file', 'max:25600', 'mimes:pptx'],
        ]);
    }

    private function storeAttachments(Report $report, array $files): void
    {
        $categories = ['bast' => 'BAST', 'lpj' => 'LPJ', 'dokumentasi' => 'DOKUMENTASI'];
        foreach ($categories as $input => $category) {
            if (! isset($files[$input])) {
                continue;
            }
            $file = $files[$input];
            $path = $file->store('reports/'.$report->id.'/v'.$report->versi, 'local');
            ReportAttachment::create([
                'report_id' => $report->id,
                'kategori' => $category,
                'versi' => $report->versi,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size_bytes' => $file->getSize(),
            ]);
        }
    }

    private function assertCpclRegion(Request $request, CpclRecord $cpcl): void
    {
        abort_unless($cpcl->aktif && $request->user()->provinsi === $cpcl->provinsi && $request->user()->kabupaten === $cpcl->kabupaten, 404);
    }

    private function assertEditable(Request $request, Report $report): void
    {
        abort_unless($report->user_id === $request->user()->id, 404);
        abort_unless($report->status === 'DRAF' && $report->cpcl_id !== null, 404);
    }
}
