<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\CpclRecord;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CpclSelectionController extends Controller
{
    public function __invoke(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'detail' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $user = $request->user();
        $regionAssigned = filled($user->provinsi) && filled($user->kabupaten);
        $query = CpclRecord::query()->where('aktif', true)
            ->where('provinsi', $user->provinsi)
            ->where('kabupaten', $user->kabupaten)
            ->with(['reports' => fn ($reports) => $reports->where('user_id', $user->id)->latest()]);

        $search = trim((string) $request->query('q', ''));
        $district = trim((string) $request->query('kecamatan', ''));
        if ($search !== '') {
            $query->where(function ($records) use ($search): void {
                foreach (['nama_di', 'kel_tani', 'no', 'provinsi', 'kabupaten', 'kecamatan', 'desa', 'keterangan', 'kewenangan'] as $field) {
                    $records->orWhere($field, 'like', '%'.$search.'%');
                }
            });
        }
        if ($district !== '') {
            $query->where('kecamatan', $district);
        }

        $selectedCpcl = null;
        if ($regionAssigned && $request->filled('detail')) {
            $selectedCpcl = CpclRecord::query()->where('aktif', true)
                ->where('provinsi', $user->provinsi)->where('kabupaten', $user->kabupaten)
                ->with(['reports' => fn ($reports) => $reports->where('user_id', $user->id)])
                ->find($request->integer('detail'));
        }

        return view('reports.cpcl-index', [
            'records' => $query->orderBy('nama_di')->orderBy('kel_tani')->orderBy('id')->paginate(20)->withQueryString(),
            'regionAssigned' => $regionAssigned,
            'filters' => ['q' => $search, 'kecamatan' => $district],
            'districts' => $regionAssigned ? CpclRecord::query()->where('aktif', true)->where('provinsi', $user->provinsi)->where('kabupaten', $user->kabupaten)->distinct()->orderBy('kecamatan')->pluck('kecamatan') : collect(),
            'selectedCpcl' => $selectedCpcl,
        ]);
    }
}
