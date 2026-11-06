<?php

namespace App\Http\Controllers;

use App\Models\CpclRecord;
use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReporterReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $this->filters($request);
        $query = $user->reports()->getQuery()->with(['cpclRecord', 'verification']);
        $this->applyFilters($query, $filters);
        $cpclOptions = CpclRecord::query()
            ->select(['kel_tani', 'nama_di'])
            ->whereHas('reports', fn (Builder $reports) => $reports->where('user_id', $user->id))
            ->distinct()
            ->get();

        return view('reports.index', [
            'reports' => $query->paginate(20)->withQueryString(),
            'filters' => $filters,
            'provinces' => $user->provinsi ? [$user->provinsi] : [],
            'groups' => $cpclOptions->pluck('kel_tani')->filter()->unique()->sort()->values(),
            'irrigations' => $cpclOptions->pluck('nama_di')->filter()->unique()->sort()->values(),
            'districts' => $user->reports()->distinct()->whereNotNull('kecamatan')->orderBy('kecamatan')->pluck('kecamatan'),
            'villages' => $user->reports()->when($filters['kecamatan'] !== '', fn ($q) => $q->where('kecamatan', $filters['kecamatan']))->distinct()->whereNotNull('desa')->orderBy('desa')->pluck('desa'),
        ]);
    }

    public function all(Request $request): View
    {
        $filters = $this->filters($request);
        $query = Report::query()->where('status', 'TERKIRIM')->with(['cpclRecord', 'verification']);
        $this->applyFilters($query, $filters);

        return view('reports.all', [
            'reports' => $query->paginate(20)->withQueryString(),
            'filters' => $filters,
            'provinces' => Report::query()->where('status', 'TERKIRIM')->distinct()->orderBy('provinsi')->pluck('provinsi'),
            'regencies' => Report::query()->where('status', 'TERKIRIM')->when($filters['provinsi'] !== '', fn ($q) => $q->where('provinsi', $filters['provinsi']))->distinct()->orderBy('kabupaten')->pluck('kabupaten'),
        ]);
    }

    private function filters(Request $request): array
    {
        $rules = [];
        foreach (['q', 'provinsi', 'kelompok_tani', 'nama_di', 'kecamatan', 'desa', 'kabupaten'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:255'];
        }
        $rules['status'] = ['nullable', 'string', 'in:DRAF,Menunggu pemeriksaan,Tidak sesuai,Sesuai'];
        $rules['sort'] = ['nullable', 'string', 'in:date-desc,date-asc,group-asc,group-desc,di-asc,di-desc,kab-asc,kab-desc'];
        $rules['date_from'] = ['nullable', 'date_format:Y-m-d'];
        $rules['date_to'] = ['nullable', 'date_format:Y-m-d'];
        if ($request->filled('date_from')) {
            $rules['date_to'][] = 'after_or_equal:date_from';
        }
        $rules['page'] = ['nullable', 'integer', 'min:1'];
        $request->validate($rules);

        return [
            'q' => trim((string) $request->query('q', '')),
            'provinsi' => trim((string) $request->query('provinsi', '')),
            'kelompok_tani' => trim((string) $request->query('kelompok_tani', '')),
            'nama_di' => trim((string) $request->query('nama_di', '')),
            'kecamatan' => trim((string) $request->query('kecamatan', '')),
            'desa' => trim((string) $request->query('desa', '')),
            'kabupaten' => trim((string) $request->query('kabupaten', '')),
            'status' => trim((string) $request->query('status', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
            'sort' => trim((string) $request->query('sort', 'date-desc')),
        ];
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $query->when($filters['q'] !== '', function (Builder $builder) use ($filters): void {
            $term = '%'.$filters['q'].'%';
            $builder->where(function (Builder $nested) use ($term): void {
                $nested->where('nomor_tiket', 'like', $term)
                    ->orWhere('jenis_kerusakan', 'like', $term)
                    ->orWhere('kabupaten', 'like', $term)
                    ->orWhere('provinsi', 'like', $term)
                    ->orWhere('kecamatan', 'like', $term)
                    ->orWhere('desa', 'like', $term)
                    ->orWhere('nama_saluran', 'like', $term)
                    ->orWhereHas('cpclRecord', fn (Builder $cpcl) => $cpcl->where('kel_tani', 'like', $term)->orWhere('nama_di', 'like', $term));
            });
        });
        foreach (['provinsi', 'kabupaten', 'kecamatan', 'desa'] as $field) {
            if ($filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }
        if ($filters['kelompok_tani'] !== '') {
            $query->whereHas('cpclRecord', fn (Builder $cpcl) => $cpcl->where('kel_tani', $filters['kelompok_tani']));
        }
        if ($filters['nama_di'] !== '') {
            $query->whereHas('cpclRecord', fn (Builder $cpcl) => $cpcl->where('nama_di', $filters['nama_di']));
        }
        if ($filters['date_from'] !== '') {
            $query->whereDate('dikirim_pada', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] !== '') {
            $query->whereDate('dikirim_pada', '<=', $filters['date_to']);
        }
        if ($filters['status'] === 'DRAF') {
            $query->where('status', 'DRAF');
        } elseif ($filters['status'] === 'Menunggu pemeriksaan') {
            $query->where('status', 'TERKIRIM')->whereDoesntHave('verification');
        } elseif ($filters['status'] === 'Tidak sesuai') {
            $query->whereHas('verification', fn (Builder $verification) => $verification->where('hasil', 'TIDAK_SESUAI'));
        } elseif ($filters['status'] === 'Sesuai') {
            $query->whereHas('verification', fn (Builder $verification) => $verification->where('hasil', 'SESUAI'));
        }

        match ($filters['sort']) {
            'date-asc' => $query->orderBy('updated_at'),
            'group-asc' => $query->orderBy(CpclRecord::query()->select('kel_tani')->whereColumn('cpcl_records.id', 'reports.cpcl_id')),
            'group-desc' => $query->orderByDesc(CpclRecord::query()->select('kel_tani')->whereColumn('cpcl_records.id', 'reports.cpcl_id')),
            'di-asc' => $query->orderBy(CpclRecord::query()->select('nama_di')->whereColumn('cpcl_records.id', 'reports.cpcl_id')),
            'di-desc' => $query->orderByDesc(CpclRecord::query()->select('nama_di')->whereColumn('cpcl_records.id', 'reports.cpcl_id')),
            'kab-asc' => $query->orderBy('kabupaten'),
            'kab-desc' => $query->orderByDesc('kabupaten'),
            default => $query->orderByDesc('updated_at'),
        };
        $query->orderByDesc('reports.id');
    }
}
