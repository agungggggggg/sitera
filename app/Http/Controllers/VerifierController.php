<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportVerification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifierController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $this->filters($request);
        $query = Report::query()->with(['user', 'cpclRecord', 'verification'])
            ->where('status', 'TERKIRIM')->whereDoesntHave('verification');
        $this->applyReportFilters($query, $filters);
        foreach (['date_from' => '>=', 'date_to' => '<='] as $key => $operator) {
            if ($filters[$key] !== '') {
                $query->whereDate('dikirim_pada', $operator, $filters[$key]);
            }
        }
        $this->applySort($query, $filters['sort']);
        $query->orderBy('reports.id');
        $reports = $query->paginate(20)->withQueryString();

        return view('verifikasi.index', [
            'reports' => $reports,
            'pendingCount' => Report::query()->where('status', 'TERKIRIM')->whereDoesntHave('verification')->count(),
            'filters' => $filters,
            'provinces' => Report::query()->where('status', 'TERKIRIM')->whereDoesntHave('verification')->whereNotNull('provinsi')->distinct()->orderBy('provinsi')->pluck('provinsi'),
            'regencies' => Report::query()->where('status', 'TERKIRIM')->whereDoesntHave('verification')->when($filters['provinsi'] !== '', fn ($q) => $q->where('provinsi', $filters['provinsi']))->whereNotNull('kabupaten')->distinct()->orderBy('kabupaten')->pluck('kabupaten'),
        ]);
    }

    public function history(Request $request): View
    {
        $filters = $this->filters($request, 'date-desc');
        $query = ReportVerification::query()->with(['report.user', 'report.cpclRecord'])
            ->where('verifier_id', $request->user()->id);
        $query->whereHas('report', function (Builder $reports) use ($filters): void {
            if ($filters['q'] !== '') {
                $reports->where(function (Builder $search) use ($filters): void {
                    $term = '%'.$filters['q'].'%';
                    $search->where('nomor_tiket', 'like', $term)
                        ->orWhere('kabupaten', 'like', $term)
                        ->orWhere('provinsi', 'like', $term)
                        ->orWhere('nama_saluran', 'like', $term)
                        ->orWhereHas('cpclRecord', fn (Builder $cpcl) => $cpcl->where('kel_tani', 'like', $term)->orWhere('nama_di', 'like', $term));
                });
            }
        });
        $query->whereHas('report', function (Builder $reports) use ($filters): void {
            foreach (['provinsi', 'kabupaten'] as $field) {
                if ($filters[$field] !== '') {
                    $reports->where($field, $filters[$field]);
                }
            }
        });
        foreach (['date_from' => '>=', 'date_to' => '<='] as $key => $operator) {
            if ($filters[$key] !== '') {
                $query->whereDate('diperiksa_pada', $operator, $filters[$key]);
            }
        }
        if ($filters['result'] !== '') {
            $query->where('hasil', $filters['result'] === 'Sesuai' ? 'SESUAI' : 'TIDAK_SESUAI');
        }
        $filters['sort'] === 'date-asc' ? $query->orderBy('diperiksa_pada') : $query->latest('diperiksa_pada');
        $reviews = $query->orderBy('id')->paginate(20)->withQueryString();

        $historyReports = Report::query()->whereHas('verification', fn ($q) => $q->where('verifier_id', $request->user()->id));
        $provinces = (clone $historyReports)->distinct()->orderBy('provinsi')->pluck('provinsi');
        $regencies = (clone $historyReports)->when($filters['provinsi'] !== '', fn ($q) => $q->where('provinsi', $filters['provinsi']))->distinct()->orderBy('kabupaten')->pluck('kabupaten');

        return view('verifikasi.history', compact('reviews', 'filters', 'provinces', 'regencies'));
    }

    private function filters(Request $request, string $sortDefault = 'date-asc'): array
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', 'max:100'],
            'result' => ['nullable', 'string', 'in:Sesuai,Tidak sesuai'],
            'sort' => ['nullable', 'string', $sortDefault === 'date-desc' ? 'in:date-asc,date-desc' : 'in:date-asc,date-desc,group-asc,group-desc'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'provinsi' => (string) ($validated['provinsi'] ?? ''),
            'kabupaten' => (string) ($validated['kabupaten'] ?? ''),
            'result' => (string) ($validated['result'] ?? ''),
            'date_from' => (string) ($validated['date_from'] ?? ''),
            'date_to' => (string) ($validated['date_to'] ?? ''),
            'sort' => (string) ($validated['sort'] ?? $sortDefault),
        ];
    }

    private function applyReportFilters(Builder $query, array $filters): void
    {
        if ($filters['q'] !== '') {
            $query->where(function (Builder $search) use ($filters): void {
                $term = '%'.$filters['q'].'%';
                $search->where('nomor_tiket', 'like', $term)
                    ->orWhere('kabupaten', 'like', $term)
                    ->orWhere('provinsi', 'like', $term)
                    ->orWhere('nama_saluran', 'like', $term)
                    ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', $term))
                    ->orWhereHas('cpclRecord', fn (Builder $cpcl) => $cpcl->where('kel_tani', 'like', $term)->orWhere('nama_di', 'like', $term));
            });
        }
        if ($filters['provinsi'] !== '') {
            $query->where('provinsi', $filters['provinsi']);
        }
        if ($filters['kabupaten'] !== '') {
            $query->where('kabupaten', $filters['kabupaten']);
        }
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'date-desc' => $query->latest('dikirim_pada'),
            'group-asc' => $query->orderBy(Report::query()->select('kel_tani')->from('cpcl_records')->whereColumn('cpcl_records.id', 'reports.cpcl_id')),
            'group-desc' => $query->orderByDesc(Report::query()->select('kel_tani')->from('cpcl_records')->whereColumn('cpcl_records.id', 'reports.cpcl_id')),
            default => $query->oldest('dikirim_pada'),
        };
    }
}
