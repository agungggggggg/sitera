<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportDetailController extends Controller
{
    public function __invoke(Request $request, Report $report): View
    {
        $user = $request->user();
        $isOwner = $report->user_id === $user->id;
        $isVerifier = $user->role?->code === 'verifikator_pusat';

        abort_unless(
            ($isOwner && $user->role?->code === 'pelapor_daerah')
                || ($isVerifier && $report->status === 'TERKIRIM'),
            404,
        );

        $report->load(['user', 'originalReport', 'correction', 'attachments', 'verification', 'cpclRecord']);

        if ($isOwner && $report->cpcl_id !== null) {
            return view('reports.package-show', ['report' => $report]);
        }

        return view('reports.show', ['report' => $report]);
    }
}
