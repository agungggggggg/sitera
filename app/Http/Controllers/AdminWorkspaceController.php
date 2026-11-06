<?php

namespace App\Http\Controllers;

use App\Models\CpclRecord;
use App\Models\User;
use App\Services\RegionCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminWorkspaceController extends Controller
{
    public function __invoke(Request $request): View
    {
        $auditData = $request->query('view') === 'audit' ? app(AuditLogController::class)->data($request) : [];
        $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'role' => ['nullable', 'string', Rule::in(['pelapor_daerah', 'verifikator_pusat', 'pimpinan_kementan', 'super_admin'])],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'pending', 'pic_pending'])],
            'view' => ['nullable', 'string', Rule::in(['dashboard', 'accounts', 'cpcl', 'imports', 'audit'])],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $usersQuery = User::query()->with('role')->orderBy('name')->orderBy('id');
        $search = trim((string) $request->query('q', ''));
        $roleFilter = (string) $request->query('role', '');
        $statusFilter = (string) $request->query('status', '');
        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('username', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('contact', 'like', '%'.$search.'%')
                    ->orWhere('provinsi', 'like', '%'.$search.'%')
                    ->orWhere('kabupaten', 'like', '%'.$search.'%');
            });
        }
        if (in_array($roleFilter, ['pelapor_daerah', 'verifikator_pusat', 'pimpinan_kementan', 'super_admin'], true)) {
            $usersQuery->whereHas('role', fn ($query) => $query->where('code', $roleFilter));
        }
        if ($statusFilter === 'active') {
            $usersQuery->where('is_active', true)->where('must_change_password', false);
        } elseif ($statusFilter === 'pending') {
            $usersQuery->where('must_change_password', true);
        } elseif ($statusFilter === 'inactive') {
            $usersQuery->where('is_active', false)->where('must_change_password', false);
        } elseif ($statusFilter === 'pic_pending') {
            $usersQuery->whereHas('role', fn ($query) => $query->where('code', 'pelapor_daerah'))
                ->whereNotNull('contact')->where('contact', '!=', '')
                ->whereNull('pic_verified_at');
        }

        $activeView = (string) $request->query('view', 'accounts');
        if (! in_array($activeView, ['dashboard', 'accounts', 'cpcl', 'imports', 'audit'], true)) {
            $activeView = 'accounts';
        }
        $cpclSearch = trim((string) $request->query('q', ''));
        $cpclQuery = CpclRecord::query()->latest();
        if ($activeView === 'cpcl' && $cpclSearch !== '') {
            $cpclQuery->where(function ($query) use ($cpclSearch): void {
                foreach (['no', 'nama_di', 'kewenangan', 'keterangan', 'provinsi', 'kabupaten', 'kecamatan', 'desa', 'kel_tani'] as $field) {
                    $query->orWhere($field, 'like', '%'.$cpclSearch.'%');
                }
            });
        }

        $cpclProvince = (string) $request->query('provinsi', '');
        $cpclRegency = (string) $request->query('kabupaten', '');
        $cpclQuery->when($cpclProvince !== '', fn ($q) => $q->where('provinsi', $cpclProvince))
            ->when($cpclRegency !== '', fn ($q) => $q->where('kabupaten', $cpclRegency))->orderBy('id');

        return view('admin-only', [
            'users' => $usersQuery->paginate(20)->withQueryString(),
            'totalUsers' => User::query()->count(),
            'activeView' => $activeView,
            'filters' => ['q' => $search, 'role' => $roleFilter, 'status' => $statusFilter],
            'cpclSearch' => $cpclSearch,
            'cpclProvince' => $cpclProvince,
            'cpclRegency' => $cpclRegency,
            'cpclProvinces' => CpclRecord::query()->distinct()->orderBy('provinsi')->pluck('provinsi'),
            'cpclRegencies' => CpclRecord::query()->when($cpclProvince !== '', fn ($q) => $q->where('provinsi', $cpclProvince))->distinct()->orderBy('kabupaten')->pluck('kabupaten'),
            'cpclRecords' => $cpclQuery->paginate(25)->withQueryString(),
            'cpclTotal' => CpclRecord::count(),
            'provinces' => app(RegionCatalog::class)->provinceNames(),
            'regionCatalog' => app(RegionCatalog::class)->all(),
            ...$auditData,
        ]);
    }
}
