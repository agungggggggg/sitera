<?php

namespace App\Services;

use App\Models\CpclRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExecutiveReportFilters
{
    public function filters(Request $request): array
    {
        $request->merge([
            'date_from' => $request->input('date_from') ?? $request->input('dari'),
            'date_to' => $request->input('date_to') ?? $request->input('sampai'),
            'status' => $request->input('status') ?? $request->input('hasil'),
        ]);
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'desa' => ['nullable', 'string', 'max:100'],
            'kelompok' => ['nullable', 'string', 'max:150'],
            'nama_di' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', 'string', Rule::in(['DRAF', 'MENUNGGU', 'TIDAK_SESUAI', 'SESUAI'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'hasil' => ['nullable', 'string', Rule::in(['MENUNGGU', 'SESUAI', 'TIDAK_SESUAI'])],
            'sort' => ['nullable', 'string', Rule::in(['updated-desc', 'updated-asc', 'kab-asc', 'kab-desc', 'group-asc', 'group-desc', 'di-asc', 'di-desc'])],
            'chart_scope' => ['nullable', 'string', 'max:220'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $validated['q'] = trim((string) ($validated['q'] ?? ''));
        $validated['date_from'] = $validated['date_from'] ?? $validated['dari'] ?? null;
        $validated['date_to'] = $validated['date_to'] ?? $validated['sampai'] ?? null;
        $validated['status'] = $validated['status'] ?? $validated['hasil'] ?? null;
        $validated['sort'] = $validated['sort'] ?? 'updated-desc';

        return $validated;
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        if (($filters['q'] ?? '') !== '') {
            $term = '%'.$filters['q'].'%';
            $query->where(function (Builder $search) use ($term): void {
                $search->where('nomor_tiket', 'like', $term)
                    ->orWhere('provinsi', 'like', $term)
                    ->orWhere('kabupaten', 'like', $term)
                    ->orWhere('nama_saluran', 'like', $term)
                    ->orWhereHas('cpclRecord', fn (Builder $cpcl) => $cpcl->where('kel_tani', 'like', $term)->orWhere('nama_di', 'like', $term));
            });
        }
        foreach (['provinsi' => 'provinsi', 'kabupaten' => 'kabupaten', 'kecamatan' => 'kecamatan', 'desa' => 'desa'] as $key => $column) {
            if (($filters[$key] ?? '') !== '') {
                $query->where($column, $filters[$key]);
            }
        }
        if (($filters['kelompok'] ?? '') !== '') {
            $query->whereHas('cpclRecord', fn (Builder $cpcl) => $cpcl->where('kel_tani', $filters['kelompok']));
        }
        if (($filters['nama_di'] ?? '') !== '') {
            $query->whereHas('cpclRecord', fn (Builder $cpcl) => $cpcl->where('nama_di', $filters['nama_di']));
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('dikirim_pada', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('dikirim_pada', '<=', $filters['date_to']);
        }
        match ($filters['status'] ?? '') {
            'DRAF' => $query->where('status', 'DRAF'),
            'MENUNGGU' => $query->where('status', 'TERKIRIM')->whereDoesntHave('verification'),
            'SESUAI' => $query->whereHas('verification', fn (Builder $verification) => $verification->where('hasil', 'SESUAI')),
            'TIDAK_SESUAI' => $query->whereHas('verification', fn (Builder $verification) => $verification->where('hasil', 'TIDAK_SESUAI')),
            default => null,
        };
    }

    public function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'updated-asc' => $query->oldest('updated_at'),
            'kab-asc' => $query->orderBy('kabupaten'),
            'kab-desc' => $query->orderByDesc('kabupaten'),
            'group-asc' => $query->orderBy(CpclRecord::query()->select('kel_tani')->whereColumn('cpcl_records.id', 'reports.cpcl_id')),
            'group-desc' => $query->orderByDesc(CpclRecord::query()->select('kel_tani')->whereColumn('cpcl_records.id', 'reports.cpcl_id')),
            'di-asc' => $query->orderBy(CpclRecord::query()->select('nama_di')->whereColumn('cpcl_records.id', 'reports.cpcl_id')),
            'di-desc' => $query->orderByDesc(CpclRecord::query()->select('nama_di')->whereColumn('cpcl_records.id', 'reports.cpcl_id')),
            default => $query->latest('updated_at'),
        };
        $query->orderBy('reports.id');
    }
}
