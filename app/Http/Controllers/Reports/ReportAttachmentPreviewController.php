<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;

use App\Models\ReportAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;

class ReportAttachmentPreviewController extends Controller
{
    public function __invoke(Request $request, ReportAttachment $attachment): BinaryFileResponse
    {
        $report = $attachment->report;
        $user = $request->user();
        $isOwner = $report->user_id === $user->id && $user->role?->code === 'pelapor_daerah';
        $isVerifierOfSubmitted = $user->role?->code === 'verifikator_pusat' && $report->status === 'TERKIRIM';
        abort_unless($isOwner || $isVerifierOfSubmitted, 404);

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        abort_unless(in_array($attachment->mime_type, $allowedMimeTypes, true), 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        $path = Storage::disk('local')->path($attachment->path);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_INLINE,
            basename($attachment->original_name),
        );

        return response()->file($path, [
            'Content-Type' => $attachment->mime_type,
            'Content-Disposition' => $disposition,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "sandbox; default-src 'none';",
        ]);
    }
}
