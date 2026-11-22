<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CpclRecord;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminCpclController extends Controller
{
    public function destroy(Request $request, CpclRecord $cpcl, AuditLogger $auditLogger): RedirectResponse
    {
        DB::transaction(function () use ($cpcl): void {
            $locked = CpclRecord::query()->lockForUpdate()->findOrFail($cpcl->id);

            if ($locked->reports()->exists()) {
                throw ValidationException::withMessages([
                    'cpcl' => 'Data CPCL tidak dapat dihapus karena sudah digunakan pada laporan.',
                ]);
            }

            $locked->delete();
        });

        $auditLogger->log($request->user(), 'cpcl.deleted', 'success', 'cpcl', $cpcl->id);

        return redirect()->route('admin.index', ['view' => 'cpcl'])
            ->with('status', 'Data CPCL berhasil dihapus.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'no' => ['nullable', 'string', 'max:30'],
            'nama_di' => ['required', 'string', 'max:150'],
            'kewenangan' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kabupaten' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'desa' => ['required', 'string', 'max:100'],
            'kel_tani' => ['required', 'string', 'max:150'],
            'nama_ketua' => ['nullable', 'string', 'max:150'],
            'realisasi' => ['nullable', 'string', 'max:100'],
            'luas_ha' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'x' => ['nullable', 'numeric', 'between:-180,180'],
            'y' => ['nullable', 'numeric', 'between:-90,90'],
        ]);

        $duplicate = CpclRecord::query()->where('provinsi', $data['provinsi'])
            ->where('kabupaten', $data['kabupaten'])
            ->where('kecamatan', $data['kecamatan'])
            ->where('desa', $data['desa'])
            ->where('kel_tani', $data['kel_tani'])->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['kel_tani' => 'Identitas CPCL dengan wilayah tersebut sudah terdaftar.']);
        }

        $data['created_by'] = $request->user()->id;
        $record = CpclRecord::create($data);
        app(AuditLogger::class)->log($request->user(), 'cpcl.created', 'success', 'cpcl', $record->id);

        return redirect('/admin-only')->with('status', 'Data CPCL berhasil ditambahkan.');
    }
}
