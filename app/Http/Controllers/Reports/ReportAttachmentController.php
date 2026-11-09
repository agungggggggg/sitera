<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;

use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportAttachmentController extends Controller
{
    public function store(Request $request, Report $report): RedirectResponse
    {
        abort_unless($report->user_id === $request->user()->id, 404);
        abort_unless($report->status === 'DRAF', 422);
        abort_if($report->attachments()->count() >= 5, 422, 'Maksimal 5 lampiran.');

        $file = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,xls,xlsx,csv'],
        ])['file'];

        $path = $file->store('reports/'.$report->id, 'local');
        $report->attachments()->create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
            'size_bytes' => $file->getSize(),
        ]);

        $target = $report->koreksi_dari ? '/laporan/'.$report->id.'/koreksi/edit' : '/laporan/'.$report->id;

        return redirect($target)->with('status', 'Lampiran berhasil ditambahkan.');
    }
}
