<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportCsvExporter
{
    public const HEADERS = [
        'Nomor Tiket',
        'Status',
        'Tanggal Kejadian',
        'Pelapor',
        'Provinsi',
        'Kabupaten',
        'Jenis Kerusakan',
        'Deskripsi',
        'Dikirim Pada',
    ];

    /**
     * Generate complete CSV text with UTF-8 BOM.
     *
     * @param  Collection|iterable  $reports
     */
    public function export(iterable $reports): string
    {
        $handle = fopen('php://memory', 'r+');
        $this->writeToStream($handle, $reports);
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content !== false ? $content : '';
    }

    /**
     * Write CSV data directly into an open resource stream.
     *
     * @param  resource  $handle
     * @param  Collection|iterable  $reports
     */
    public function writeToStream($handle, iterable $reports): void
    {
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, self::HEADERS);

        foreach ($reports as $report) {
            fputcsv($handle, $this->formatRow($report));
        }
    }

    /**
     * Build a streamed HTTP response for CSV file download.
     *
     * @param  Collection|iterable  $reports
     */
    public function response(iterable $reports, ?string $filename = null): StreamedResponse
    {
        $targetFilename = $filename ?? 'sitera-rekap-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($reports): void {
            $handle = fopen('php://output', 'w');
            $this->writeToStream($handle, $reports);
            fclose($handle);
        }, $targetFilename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Transform a single report instance to ordered CSV row fields.
     *
     * @param  mixed  $report
     * @return array<int, string|null>
     */
    public function formatRow(mixed $report): array
    {
        return [
            $report->nomor_tiket,
            $report->status,
            $report->tanggal_kejadian?->format('Y-m-d'),
            $report->user?->name,
            $report->provinsi,
            $report->kabupaten,
            $report->jenis_kerusakan,
            $report->deskripsi,
            $report->dikirim_pada?->format('Y-m-d H:i:s'),
        ];
    }
}
