<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CpclRecord;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $request->validate(['cakupan' => ['nullable', 'string', 'max:220']]);
        $scope = (string) $request->query('cakupan', 'Nasional');
        [$scopeType, $scopeValue] = array_pad(explode('|', $scope, 2), 2, '');
        $scopeProvince = '';
        if ($scopeType === 'kabupaten') {
            [$scopeProvince, $scopeValue] = array_pad(explode('~', $scopeValue, 2), 2, '');
        }
        if ($scopeType === 'provinsi' && ! CpclRecord::query()->where('provinsi', $scopeValue)->exists()) {
            $scope = 'Nasional';
            $scopeType = 'Nasional';
            $scopeValue = '';
        } elseif ($scopeType === 'kabupaten' && ! CpclRecord::query()->where('provinsi', $scopeProvince)->where('kabupaten', $scopeValue)->exists()) {
            $scope = 'Nasional';
            $scopeType = 'Nasional';
            $scopeValue = '';
            $scopeProvince = '';
        }
        $scopeRecords = fn () => CpclRecord::query()
            ->when($scopeType === 'provinsi', fn ($query) => $query->where('provinsi', $scopeValue))
            ->when($scopeType === 'kabupaten', fn ($query) => $query->where('provinsi', $scopeProvince)->where('kabupaten', $scopeValue));
        $scopeOptions = collect([['value' => 'Nasional', 'label' => 'Nasional']])
            ->merge(CpclRecord::query()->select('provinsi')->distinct()->orderBy('provinsi')->get()
                ->map(fn ($row) => ['value' => 'provinsi|'.$row->provinsi, 'label' => $row->provinsi]))
            ->merge(CpclRecord::query()->select('provinsi', 'kabupaten')->distinct()->orderBy('provinsi')->orderBy('kabupaten')->get()
                ->map(fn ($row) => ['value' => 'kabupaten|'.$row->provinsi.'~'.$row->kabupaten, 'label' => $row->kabupaten.' · '.$row->provinsi]));
        $allCpclStatus = $scopeRecords()
            ->withCount('reports')
            ->withCount(['reports as draft_reports_count' => fn ($query) => $query->where('status', 'DRAF')])
            ->withCount(['reports as submitted_reports_count' => fn ($query) => $query->where('status', 'TERKIRIM')])
            ->withCount(['reports as sesuai_reports_count' => fn ($query) => $query->whereHas('verification', fn ($verification) => $verification->where('hasil', 'SESUAI'))])
            ->withCount(['reports as tidak_sesuai_reports_count' => fn ($query) => $query->whereHas('verification', fn ($verification) => $verification->where('hasil', 'TIDAK_SESUAI'))])
            ->get();

        $recentCpcl = $scopeRecords()
            ->withCount('reports')
            ->withCount(['reports as draft_reports_count' => fn ($query) => $query->where('status', 'DRAF')])
            ->withCount(['reports as submitted_reports_count' => fn ($query) => $query->where('status', 'TERKIRIM')])
            ->withCount(['reports as sesuai_reports_count' => fn ($query) => $query->whereHas('verification', fn ($verification) => $verification->where('hasil', 'SESUAI'))])
            ->withCount(['reports as tidak_sesuai_reports_count' => fn ($query) => $query->whereHas('verification', fn ($verification) => $verification->where('hasil', 'TIDAK_SESUAI'))])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        $statusCounts = ['Belum ada laporan' => 0, 'Draf' => 0, 'Menunggu pemeriksaan' => 0, 'Tidak sesuai' => 0, 'Sesuai' => 0];
        foreach ($allCpclStatus as $record) {
            $status = match (true) {
                $record->reports_count === 0 => 'Belum ada laporan',
                $record->draft_reports_count > 0 => 'Draf',
                $record->tidak_sesuai_reports_count > 0 => 'Tidak sesuai',
                $record->sesuai_reports_count > 0 => 'Sesuai',
                default => 'Menunggu pemeriksaan',
            };
            $statusCounts[$status]++;
        }

        $chartColors = ['#879ba5', '#086288', '#d49a3a', '#b9504c', '#39845a'];
        $chartTotal = array_sum($statusCounts);
        $circumference = 2 * pi() * 82;
        $dashOffset = 0.0;
        $chartSegments = [];
        foreach ($statusCounts as $statusLabel => $statusCount) {
            if ($statusCount < 1 || $chartTotal < 1) {
                continue;
            }
            $dashLength = $statusCount / $chartTotal * $circumference;
            $chartSegments[] = ['color' => $chartColors[count($chartSegments)], 'dash' => $dashLength, 'offset' => -$dashOffset];
            $dashOffset += $dashLength;
        }

        return view('admin.dashboard', [
            'chartColors' => $chartColors,
            'chartTotal' => $chartTotal,
            'circumference' => $circumference,
            'chartSegments' => $chartSegments,
            'totalUsers' => User::query()->count(),
            'reporterCount' => User::query()->whereHas('role', fn ($query) => $query->where('code', 'pelapor_daerah'))->count(),
            'verifierCount' => User::query()->whereHas('role', fn ($query) => $query->where('code', 'verifikator_pusat'))->count(),
            'executiveCount' => User::query()->whereHas('role', fn ($query) => $query->where('code', 'pimpinan_kementan'))->count(),
            'cpclCount' => CpclRecord::query()->count(),
            'draftCount' => Report::query()->whereNotNull('cpcl_id')->where('status', 'DRAF')->count(),
            'submittedCount' => Report::query()->whereNotNull('cpcl_id')->where('status', 'TERKIRIM')->count(),
            'statusCounts' => $statusCounts,
            'scope' => $scope,
            'scopeOptions' => $scopeOptions,
            'recentUsers' => User::query()->with('role')->latest()->limit(6)->get(),
            'recentCpcl' => $recentCpcl,
        ]);
    }
}
