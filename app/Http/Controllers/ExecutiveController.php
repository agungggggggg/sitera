<?php

namespace App\Http\Controllers;

use App\Models\CpclRecord;
use App\Models\Report;
use App\Services\ExecutiveReportFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ExecutiveController extends Controller
{
    public function dashboard(Request $request): View
    {
        return view('monitoring.dashboard', $this->viewData($request));
    }

    public function __invoke(Request $request): View
    {
        return view('monitoring.index', $this->viewData($request));
    }

    public function show(Request $request, Report $report): View
    {
        $report->load(['user', 'cpclRecord', 'verification.verifier', 'attachments']);

        return view('monitoring.show', ['report' => $report]);
    }

    private function viewData(Request $request): array
    {
        $filters = app(ExecutiveReportFilters::class)->filters($request);
        $query = Report::query()->with(['user', 'cpclRecord', 'verification', 'attachments']);
        app(ExecutiveReportFilters::class)->applyFilters($query, $filters);
        $total = (clone $query)->count();
        $drafts = (clone $query)->where('status', 'DRAF')->count();
        $pending = (clone $query)->where('status', 'TERKIRIM')->whereDoesntHave('verification')->count();
        $compliant = (clone $query)->whereHas('verification', fn (Builder $verification) => $verification->where('hasil', 'SESUAI'))->count();
        $nonCompliant = (clone $query)->whereHas('verification', fn (Builder $verification) => $verification->where('hasil', 'TIDAK_SESUAI'))->count();
        $sent = (clone $query)->where('status', 'TERKIRIM')->count();
        $byProvince = (clone $query)->select('provinsi')->selectRaw('COUNT(*) as aggregate')->groupBy('provinsi')->orderByDesc('aggregate')->pluck('aggregate', 'provinsi');
        $byDamage = (clone $query)->select('jenis_kerusakan')->selectRaw('COUNT(*) as aggregate')->groupBy('jenis_kerusakan')->orderByDesc('aggregate')->pluck('aggregate', 'jenis_kerusakan');
        $completeness = [];
        foreach (['BAST', 'LPJ', 'DOKUMENTASI'] as $category) {
            $completeness[$category] = (clone $query)->whereHas('attachments', fn (Builder $attachments) => $attachments
                ->where('kategori', $category)->whereColumn('report_attachments.versi', 'reports.versi'))->count();
        }
        $completePackages = (clone $query)
            ->whereHas('attachments', fn (Builder $attachments) => $attachments->where('kategori', 'BAST')->whereColumn('report_attachments.versi', 'reports.versi'))
            ->whereHas('attachments', fn (Builder $attachments) => $attachments->where('kategori', 'LPJ')->whereColumn('report_attachments.versi', 'reports.versi'))
            ->whereHas('attachments', fn (Builder $attachments) => $attachments->where('kategori', 'DOKUMENTASI')->whereColumn('report_attachments.versi', 'reports.versi'))
            ->count();

        $reportSort = (clone $query);
        app(ExecutiveReportFilters::class)->applySort($reportSort, $filters['sort']);
        $reports = $reportSort->paginate(15)->withQueryString();
        $latest = (clone $query)->latest('updated_at')->limit(8)->get();
        $oldestQueue = (clone $query)->where('status', 'TERKIRIM')->whereDoesntHave('verification')->oldest('dikirim_pada')->limit(5)->get();
        $followUp = (clone $query)->whereHas('verification', fn (Builder $verification) => $verification->where('hasil', 'TIDAK_SESUAI'))->latest('updated_at')->limit(5)->get();

        [$cpclTotal, $cpclCounts, $chartScope, $scopeOptions, $chartScopeLabel] = $this->cpclSummary($request);

        $exportFilters = array_filter(collect($filters)->except(['chart_scope', 'page', 'dari', 'sampai', 'hasil'])->all(), fn ($value) => $value !== null && $value !== '');

        return [
            'exportUrl' => route('exports.csv', array_merge(['format' => 'pdf'], $exportFilters)),
            'exportAvailable' => ($filters['status'] ?? '') !== 'DRAF',
            'filters' => $filters,
            'reports' => $reports,
            'latest' => $latest,
            'oldestQueue' => $oldestQueue,
            'followUp' => $followUp,
            'totalReports' => $total,
            'draftReports' => $drafts,
            'submittedReports' => $sent,
            'pendingReports' => $pending,
            'compliantReports' => $compliant,
            'nonCompliantReports' => $nonCompliant,
            'byProvince' => $byProvince,
            'byDamage' => $byDamage,
            'completeness' => $completeness,
            'completePackages' => $completePackages,
            'provinces' => CpclRecord::query()->whereNotNull('provinsi')->distinct()->pluck('provinsi')->merge(Report::query()->whereNotNull('provinsi')->distinct()->pluck('provinsi'))->unique()->sort()->values(),
            'regencies' => $this->options('kabupaten', $filters, ['provinsi']),
            'districts' => $this->options('kecamatan', $filters, ['provinsi', 'kabupaten']),
            'villages' => $this->options('desa', $filters, ['provinsi', 'kabupaten', 'kecamatan']),
            'groups' => $this->options('kel_tani', $filters, ['provinsi', 'kabupaten', 'kecamatan', 'desa']),
            'irrigationAreas' => $this->options('nama_di', $filters, ['provinsi', 'kabupaten', 'kecamatan', 'desa']),
            'cpclTotal' => $cpclTotal,
            'cpclCounts' => $cpclCounts,
            'chartScope' => $chartScope,
            'scopeOptions' => $scopeOptions,
            'chartScopeLabel' => $chartScopeLabel,
        ];
    }

    private function options(string $column, array $filters, array $parents): Collection
    {
        $cpcl = CpclRecord::query();
        $reports = Report::query();
        foreach ($parents as $parent) {
            if (! empty($filters[$parent])) {
                $cpcl->where($parent, $filters[$parent]);
                $reports->where($parent, $filters[$parent]);
            }
        }
        $values = $cpcl->whereNotNull($column)->distinct()->pluck($column);
        if (in_array($column, ['provinsi', 'kabupaten', 'kecamatan', 'desa'], true)) {
            $values = $values->merge($reports->whereNotNull($column)->distinct()->pluck($column));
        }

        return $values->filter()->unique()->sort()->values();
    }

    private function cpclSummary(Request $request): array
    {
        $options = collect([['value' => 'Nasional', 'label' => 'Nasional']])
            ->merge(CpclRecord::query()->where('aktif', true)->whereNotNull('provinsi')->select('provinsi')->distinct()->orderBy('provinsi')->get()
                ->map(fn ($row) => ['value' => 'provinsi|'.$row->provinsi, 'label' => $row->provinsi]))
            ->merge(CpclRecord::query()->where('aktif', true)->whereNotNull('kabupaten')->select('provinsi', 'kabupaten')->distinct()->orderBy('provinsi')->orderBy('kabupaten')->get()
                ->map(fn ($row) => ['value' => 'kabupaten|'.$row->provinsi.'~'.$row->kabupaten, 'label' => $row->kabupaten.' · '.$row->provinsi]));
        $scope = (string) ($request->query('chart_scope', 'Nasional'));
        if (! $options->contains('value', $scope)) {
            $scope = 'Nasional';
        }
        [$type, $value] = array_pad(explode('|', $scope, 2), 2, '');
        [$province, $regency] = $type === 'kabupaten' ? array_pad(explode('~', $value, 2), 2, '') : ['', ''];
        $cpcl = CpclRecord::query()->where('aktif', true)
            ->when($type === 'provinsi', fn ($query) => $query->where('provinsi', $value))
            ->when($type === 'kabupaten', fn ($query) => $query->where('provinsi', $province)->where('kabupaten', $regency))
            ->with(['reports' => fn ($reports) => $reports->where('status', 'TERKIRIM')->with('verification')->latest('dikirim_pada')])
            ->get();
        $counts = ['sesuai' => 0, 'belum_sesuai' => 0, 'belum_melapor' => 0];
        foreach ($cpcl as $record) {
            $latestSent = $record->reports->first();
            if (! $latestSent) {
                $counts['belum_melapor']++;
            } elseif ($latestSent->verification?->hasil === 'SESUAI') {
                $counts['sesuai']++;
            } else {
                $counts['belum_sesuai']++;
            }
        }

        return [$cpcl->count(), $counts, $scope, $options, $options->firstWhere('value', $scope)['label'] ?? 'Nasional'];
    }
}
