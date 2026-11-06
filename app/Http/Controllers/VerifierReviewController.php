<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifierReviewController extends Controller
{
    public function show(Request $request, Report $report): View
    {
        abort_unless($report->status === 'TERKIRIM', 404);

        return view('verifikasi.show', ['report' => $report->load(['user', 'cpclRecord', 'attachments', 'verification.verifier'])]);
    }
}
