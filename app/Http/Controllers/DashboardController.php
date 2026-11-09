<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Monitoring\ExecutiveController;
use App\Http\Controllers\Reports\ReporterDashboardController;
use App\Http\Controllers\Verification\VerifierDashboardController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        if ($user->role?->code === 'super_admin') {
            return app(AdminDashboardController::class)($request);
        }
        if ($user->role?->code === 'pelapor_daerah') {
            return app(ReporterDashboardController::class)($request);
        }
        if ($user->role?->code === 'verifikator_pusat') {
            return app(VerifierDashboardController::class)($request);
        }
        if ($user->role?->code === 'pimpinan_kementan') {
            return app(ExecutiveController::class)->dashboard($request);
        }

        return $this->fallbackDashboard($user);
    }

    private function fallbackDashboard(User $user): View
    {
        $reports = $user->reports()->with('cpclRecord')->latest()->get();

        return view('dashboard', [
            'reports' => $reports,
            'totalReports' => $reports->count(),
            'draftReports' => $reports->where('status', 'DRAF')->count(),
            'submittedReports' => $reports->where('status', 'TERKIRIM')->count(),
            'latestReport' => $reports->first(),
        ]);
    }
}
