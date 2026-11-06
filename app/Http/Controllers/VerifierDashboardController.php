<?php

namespace App\Http\Controllers;

use App\Models\CpclRecord;
use App\Models\Report;
use App\Models\ReportVerification;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifierDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $request->validate(['cakupan' => ['nullable', 'string', 'max:220']]);
        $scope = (string) $request->query('cakupan', 'Nasional');
        $scopeOptions = collect([['value' => 'Nasional', 'label' => 'Nasional']])
            ->merge(CpclRecord::query()->whereNotNull('provinsi')->select('provinsi')->distinct()->orderBy('provinsi')->get()
                ->map(fn ($row) => ['value' => 'provinsi|'.$row->provinsi, 'label' => $row->provinsi]))
            ->merge(CpclRecord::query()->whereNotNull('kabupaten')->select('provinsi', 'kabupaten')->distinct()->orderBy('provinsi')->orderBy('kabupaten')->get()
                ->map(fn ($row) => ['value' => 'kabupaten|'.$row->provinsi.'~'.$row->kabupaten, 'label' => $row->kabupaten.' · '.$row->provinsi]));
        if (! $scopeOptions->contains('value', $scope)) {
            $scope = 'Nasional';
        }
        [$scopeType, $scopeValue] = array_pad(explode('|', $scope, 2), 2, '');
        [$scopeProvince, $scopeRegency] = $scopeType === 'kabupaten' ? array_pad(explode('~', $scopeValue, 2), 2, '') : ['', ''];
        $cpclRecords = CpclRecord::query()
            ->when($scopeType === 'provinsi', fn ($query) => $query->where('provinsi', $scopeValue))
            ->when($scopeType === 'kabupaten', fn ($query) => $query->where('provinsi', $scopeProvince)->where('kabupaten', $scopeRegency))
            ->with(['reports' => fn ($query) => $query->with('verification')->latest('dikirim_pada')])
            ->get();
        $cpclCounts = ['sesuai' => 0, 'belum_sesuai' => 0, 'belum_melapor' => 0];
        foreach ($cpclRecords as $cpcl) {
            $latestSent = $cpcl->reports->first(fn ($report) => $report->status === 'TERKIRIM');
            if (! $latestSent) {
                $cpclCounts['belum_melapor']++;
            } elseif ($latestSent->verification?->hasil === 'SESUAI') {
                $cpclCounts['sesuai']++;
            } else {
                $cpclCounts['belum_sesuai']++;
            }
        }
        $pending = Report::query()->where('status', 'TERKIRIM')->whereDoesntHave('verification');
        $recentQueue = (clone $pending)->with(['user', 'cpclRecord'])->oldest('dikirim_pada')->limit(5)->get();
        $followUp = Report::query()->whereHas('verification', fn ($query) => $query->where('hasil', 'TIDAK_SESUAI'))
            ->with(['verification', 'cpclRecord'])->latest('updated_at')->limit(5)->get();

        return view('verifikasi.dashboard', [
            'pendingCount' => (clone $pending)->count(),
            'compliantCount' => Report::query()->whereHas('verification', fn ($query) => $query->where('hasil', 'SESUAI'))->count(),
            'nonCompliantCount' => Report::query()->whereHas('verification', fn ($query) => $query->where('hasil', 'TIDAK_SESUAI'))->count(),
            'reviewedCount' => ReportVerification::query()->count(),
            'recentQueue' => $recentQueue,
            'followUp' => $followUp,
            'cpclTotal' => $cpclRecords->count(),
            'cpclCounts' => $cpclCounts,
            'scope' => $scope,
            'scopeOptions' => $scopeOptions,
        ]);
    }
}
