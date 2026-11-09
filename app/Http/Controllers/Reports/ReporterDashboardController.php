<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReporterDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $reports = $user->reports()->with(['cpclRecord', 'verification', 'attachments'])->latest('updated_at')->get();
        $statusFor = static fn (Report $report): string => match (true) {
            $report->status === 'DRAF' => 'Draf',
            $report->verification?->hasil === 'SESUAI' => 'Sesuai',
            $report->verification?->hasil === 'TIDAK_SESUAI' => 'Tidak sesuai',
            default => 'Menunggu pemeriksaan',
        };
        $statusCounts = ['Draf' => 0, 'Menunggu pemeriksaan' => 0, 'Tidak sesuai' => 0, 'Sesuai' => 0];
        $documentCounts = ['BAST' => 0, 'LPJ' => 0, 'DOKUMENTASI' => 0];
        foreach ($reports as $report) {
            $statusCounts[$statusFor($report)]++;
            $currentCategories = $report->attachments->where('versi', $report->versi)->pluck('kategori')->unique();
            foreach ($documentCounts as $category => $count) {
                if ($currentCategories->contains($category)) {
                    $documentCounts[$category]++;
                }
            }
        }

        return view('reports.dashboard', [
            'reports' => $reports,
            'statusCounts' => $statusCounts,
            'documentCounts' => $documentCounts,
            'statusFor' => $statusFor,
        ]);
    }
}
