<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\ExecutiveReportFilters;
use App\Services\RegionCatalog;
use App\Services\ReportPdfExporter;
use App\Services\ReportXlsxExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function download(Request $request, ReportXlsxExporter $xlsxExporter, ReportPdfExporter $pdfExporter, RegionCatalog $regions): Response|View
    {
        $role = $request->user()->role?->code;
        abort_unless(in_array($role, ['pelapor_daerah', 'verifikator_pusat', 'pimpinan_kementan'], true), 403);

        if (! $request->filled('format')) {
            return view('exports.index');
        }

        $format = $request->input('format');
        abort_unless(in_array($format, ['csv', 'xlsx', 'pdf'], true), 422);

        $query = Report::query()->with('user')->where('status', 'TERKIRIM');
        $role = $request->user()->role?->code;

        if ($role === 'pelapor_daerah') {
            $query->where('user_id', $request->user()->id);
        } elseif (! in_array($role, ['verifikator_pusat', 'pimpinan_kementan'], true)) {
            abort(403);
        }

        $filters = app(ExecutiveReportFilters::class)->filters($request);
        app(ExecutiveReportFilters::class)->applyFilters($query, $filters);
        app(ExecutiveReportFilters::class)->applySort($query, $filters['sort']);

        $reports = $query->get();

        if ($format === 'pdf') {
            return response($pdfExporter->export($reports), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="sitera-rekap-'.now()->format('Ymd-His').'.pdf"',
            ]);
        }

        return $format === 'xlsx'
            ? response($xlsxExporter->export($reports), 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="sitera-rekap-'.now()->format('Ymd-His').'.xlsx"',
            ])
            : $this->csvResponse($reports);
    }

    public function csv(Request $request): StreamedResponse
    {
        $reports = $this->queryFor($request)->latest('dikirim_pada')->get();

        return $this->csvResponse($reports);
    }

    private function queryFor(Request $request)
    {
        abort_unless($request->input('format', 'csv') === 'csv', 422);
        $query = Report::query()->with('user')->where('status', 'TERKIRIM');
        $role = $request->user()->role?->code;
        if ($role === 'pelapor_daerah') {
            $query->where('user_id', $request->user()->id);
        } elseif (! in_array($role, ['verifikator_pusat', 'pimpinan_kementan'], true)) {
            abort(403);
        }
        $filters = app(ExecutiveReportFilters::class)->filters($request);
        app(ExecutiveReportFilters::class)->applyFilters($query, $filters);

        return $query;
    }

    private function csvResponse($reports): StreamedResponse
    {
        $filename = 'sitera-rekap-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($reports): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Nomor Tiket', 'Status', 'Tanggal Kejadian', 'Pelapor', 'Provinsi', 'Kabupaten', 'Jenis Kerusakan', 'Deskripsi', 'Dikirim Pada']);
            foreach ($reports as $report) {
                fputcsv($handle, [
                    $report->nomor_tiket,
                    $report->status,
                    $report->tanggal_kejadian?->format('Y-m-d'),
                    $report->user?->name,
                    $report->provinsi,
                    $report->kabupaten,
                    $report->jenis_kerusakan,
                    $report->deskripsi,
                    $report->dikirim_pada?->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
