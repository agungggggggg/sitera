<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Notifications\SiteraEventNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportCorrectionController extends Controller
{
    public function create(Request $request, Report $report): RedirectResponse
    {
        abort_unless($report->user_id === $request->user()->id, 404);
        abort_unless($report->verification?->hasil === 'TIDAK_SESUAI', 422);

        $correction = Report::firstOrCreate(
            ['koreksi_dari' => $report->id],
            [
                'user_id' => $request->user()->id,
                'nomor_tiket' => $this->ticket($report),
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
            ],
        );

        return redirect('/laporan/'.$correction->id.'/koreksi/edit');
    }

    public function edit(Request $request, Report $report): View
    {
        abort_unless($report->user_id === $request->user()->id && $report->koreksi_dari && $report->status === 'DRAF', 404);

        return view('reports.correction-edit', ['report' => $report]);
    }

    public function submit(Request $request, Report $report): RedirectResponse
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
        $report->forceFill(['status' => 'TERKIRIM', 'dikirim_pada' => now()])->save();

        $verifiers = User::query()->whereHas('role', fn ($query) => $query->where('code', 'verifikator_pusat'))->get();
        foreach ($verifiers as $verifier) {
            $verifier->notify(new SiteraEventNotification(
                'koreksi_dikirim',
                'Koreksi laporan baru',
                $report->nomor_tiket.' telah dikirim dan menunggu pemeriksaan.',
                $report->id,
                $report->nomor_tiket,
            ));
        }

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

    private function ticket(Report $report): string
    {
        $date = Carbon::parse($report->tanggal_kejadian)->format('Ymd');
        $count = Report::where('nomor_tiket', 'like', 'LP-'.$date.'-%')->count() + 1;

        return 'LP-'.$date.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}

// The correction remains a draft until the separate submit slice is implemented.
