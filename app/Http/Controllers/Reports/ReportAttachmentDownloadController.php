<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;

use App\Models\ReportAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportAttachmentDownloadController extends Controller
{
    public function __invoke(Request $request, ReportAttachment $attachment): StreamedResponse
    {
        $report = $attachment->report;
        $user = $request->user();
        $isOwner = $report->user_id === $user->id && $user->role?->code === 'pelapor_daerah';
        $isVerifierOfSubmitted = $user->role?->code === 'verifikator_pusat' && $report->status === 'TERKIRIM';

        abort_unless($isOwner || $isVerifierOfSubmitted, 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download(
            $attachment->path,
            $attachment->original_name,
            ['Content-Type' => $attachment->mime_type],
        );
    }
}
