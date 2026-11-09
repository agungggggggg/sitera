<?php

use App\Http\Controllers\Admin\AdminCpclController;
use App\Http\Controllers\Admin\AdminCpclImportController;
use App\Http\Controllers\Admin\AdminPasswordResetController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminWorkspaceController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Reports\CpclSelectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Monitoring\ExecutiveController;
use App\Http\Controllers\Exports\ExportController;
use App\Http\Controllers\Auth\FirstPasswordChangeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Reports\ReportAttachmentController;
use App\Http\Controllers\Reports\ReportAttachmentDownloadController;
use App\Http\Controllers\Reports\ReportAttachmentPreviewController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Reports\ReportCorrectionController;
use App\Http\Controllers\Reports\ReportDetailController;
use App\Http\Controllers\Reports\ReporterReportController;
use App\Http\Controllers\Reports\ReportPackageController;
use App\Http\Controllers\Verification\ReportVerificationController;
use App\Http\Controllers\Verification\VerifierController;
use App\Http\Controllers\Verification\VerifierReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/forgot-password', [AdminPasswordResetController::class, 'forgot'])->name('password.request');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/password/first-login', [FirstPasswordChangeController::class, 'edit'])->name('password.first-login');
    Route::post('/password/first-login', [FirstPasswordChangeController::class, 'update'])->name('password.first-login.update');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/{notification}/baca', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::middleware('role:pelapor_daerah')->group(function (): void {
        Route::get('/laporan/buat', CpclSelectionController::class)->name('reports.create');
        Route::get('/laporan/buat/{cpcl}', [ReportPackageController::class, 'create'])->name('reports.package.create');
        Route::post('/laporan/buat/{cpcl}', [ReportPackageController::class, 'store'])->name('reports.package.store');
        Route::get('/laporan/{report}/paket/edit', [ReportPackageController::class, 'edit'])->name('reports.package.edit');
        Route::put('/laporan/{report}/paket', [ReportPackageController::class, 'update'])->name('reports.package.update');
        Route::get('/laporan/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
        Route::put('/laporan/{report}', [ReportController::class, 'update'])->name('reports.update');
        Route::post('/laporan', [ReportController::class, 'store'])->name('reports.store');
        Route::post('/laporan/{report}/kirim', [ReportController::class, 'submit'])->name('reports.submit');
    });

    Route::middleware('role:pelapor_daerah')->get('/laporan', [ReporterReportController::class, 'index'])
        ->name('reports.index');
    Route::middleware('role:pelapor_daerah')->get('/laporan/all', [ReporterReportController::class, 'all'])
        ->name('reports.all');

    Route::get('/laporan/{report}', ReportDetailController::class)->name('reports.show');
    Route::get('/lampiran/{attachment}', ReportAttachmentDownloadController::class)->name('reports.attachments.download');
    Route::get('/lampiran/{attachment}/preview', ReportAttachmentPreviewController::class)->name('reports.attachments.preview');
    Route::get('/ekspor', [ExportController::class, 'download'])->name('exports.csv');

    Route::middleware('role:pelapor_daerah')->group(function (): void {
        Route::post('/laporan/{report}/koreksi', [ReportCorrectionController::class, 'create'])->name('reports.correction.create');
        Route::get('/laporan/{report}/koreksi/edit', [ReportCorrectionController::class, 'edit'])->name('reports.correction.edit');
        Route::put('/laporan/{report}/koreksi', [ReportCorrectionController::class, 'update'])->name('reports.correction.update');
        Route::post('/laporan/{report}/koreksi/kirim', [ReportCorrectionController::class, 'submit'])->name('reports.correction.submit');
        Route::post('/laporan/{report}/lampiran', [ReportAttachmentController::class, 'store'])->name('reports.attachments.store');
    });

    Route::middleware('role:verifikator_pusat')->post('/laporan/{report}/verifikasi', [ReportVerificationController::class, 'store'])
        ->name('reports.verify');

    Route::middleware('role:verifikator_pusat')->group(function (): void {
        Route::get('/verifikasi', VerifierController::class)->name('verification.index');
        Route::get('/verifikasi/riwayat', [VerifierController::class, 'history'])->name('verification.history');
        Route::get('/verifikasi/{report}', [VerifierReviewController::class, 'show'])->name('verification.show');
    });

    Route::middleware('role:pimpinan_kementan')->group(function (): void {
        Route::get('/monitoring', ExecutiveController::class)->name('monitoring.index');
        Route::get('/monitoring/{report}', [ExecutiveController::class, 'show'])->name('monitoring.show');
    });

    Route::middleware('role:super_admin')->group(function (): void {
        Route::get('/admin-only', AdminWorkspaceController::class)->name('admin.index');
        Route::get('/admin/audit', [AuditLogController::class, 'index'])->name('admin.audit.index');
        Route::post('/admin/users/{user}/reset-password', [AdminPasswordResetController::class, 'reset'])
            ->name('admin.users.reset-password');
        Route::post('/admin/users/{user}/verify-pic', [AdminUserController::class, 'verifyPic'])
            ->name('admin.users.verify-pic');
        Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])
            ->name('admin.users.destroy');
        Route::patch('/admin/users/{user}/status', [AdminUserController::class, 'setActive'])
            ->name('admin.users.status');
        Route::post('/admin/users', [AdminUserController::class, 'store'])
            ->name('admin.users.store');
        Route::post('/admin/cpcl', [AdminCpclController::class, 'store'])
            ->name('admin.cpcl.store');
        Route::get('/admin/cpcl/template.csv', [AdminCpclImportController::class, 'template'])
            ->name('admin.cpcl.import.template');
        Route::post('/admin/cpcl/import/preview', [AdminCpclImportController::class, 'preview'])
            ->name('admin.cpcl.import.preview');
        Route::post('/admin/cpcl/import/{batch}/confirm', [AdminCpclImportController::class, 'confirm'])
            ->name('admin.cpcl.import.confirm');
        Route::post('/admin/cpcl/import/{batch}/cancel', [AdminCpclImportController::class, 'cancel'])
            ->name('admin.cpcl.import.cancel');
    });
});
