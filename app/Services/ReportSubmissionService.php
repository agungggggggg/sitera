<?php

namespace App\Services;

use App\Models\Report;
use App\Models\User;
use App\Notifications\SiteraEventNotification;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReportSubmissionService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * Atomically submit a draft report, transition its status, notify verifiers, and record an audit log.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function submit(User $actor, Report $report): Report
    {
        $lockedReport = DB::transaction(function () use ($actor, $report): Report {
            $locked = Report::query()
                ->lockForUpdate()
                ->findOrFail($report->id);

            abort_unless($locked->user_id === $actor->id, 404);

            $alreadySubmittedMessage = $locked->koreksi_dari !== null
                ? 'Koreksi sudah terkirim.'
                : 'Laporan yang sudah terkirim tidak dapat dikirim ulang.';
            abort_if($locked->status !== 'DRAF', 422, $alreadySubmittedMessage);

            $this->validateAttachments($locked);

            $locked->forceFill([
                'status' => 'TERKIRIM',
                'dikirim_pada' => now(),
            ])->save();

            return $locked;
        });

        $this->notifyVerifiersSafely($lockedReport);

        $this->auditLogger->log($actor, 'report.submitted', 'success', 'report', $lockedReport->id);

        return $lockedReport;
    }

    /**
     * Validate attachment completeness based on report type.
     */
    private function validateAttachments(Report $report): void
    {
        if ($report->cpcl_id !== null) {
            $categories = $report->attachments()
                ->where('versi', $report->versi)
                ->pluck('kategori')
                ->unique()
                ->all();

            abort_unless(
                array_diff(['BAST', 'LPJ', 'DOKUMENTASI'], $categories) === [],
                422,
                'Lengkapi BAST, LPJ, dan dokumentasi sebelum mengirim laporan.'
            );
        } else {
            abort_if(
                $report->attachments()->count() < 1,
                422,
                'Minimal satu lampiran wajib ditambahkan sebelum dikirim.'
            );
        }
    }

    /**
     * Dispatch notification to all verifiers safely post-commit.
     */
    private function notifyVerifiersSafely(Report $report): void
    {
        try {
            $isCorrection = $report->koreksi_dari !== null;
            $verifiers = User::query()->whereHas('role', fn ($q) => $q->where('code', 'verifikator_pusat'))->get();

            foreach ($verifiers as $verifier) {
                $verifier->notify(new SiteraEventNotification(
                    $isCorrection ? 'koreksi_dikirim' : 'laporan_baru',
                    $isCorrection ? 'Koreksi laporan baru' : 'Laporan baru masuk',
                    $report->nomor_tiket.' telah dikirim dan menunggu pemeriksaan.',
                    $report->id,
                    $report->nomor_tiket,
                ));
            }
        } catch (Throwable) {
            // Notification delivery failure does not roll back the committed status change.
        }
    }
}
