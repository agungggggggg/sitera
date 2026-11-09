<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\AuditLog;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.partials.audit-log', $this->data($request));
    }

    /** @return array<string, mixed> */
    public function data(Request $request): array
    {
        abort_unless($request->user()?->role?->code === 'super_admin'
            && $request->user()->is_active && ! $request->user()->must_change_password, 403);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'action' => ['nullable', 'string', Rule::in(AuditLog::ACTIONS)],
            'result' => ['nullable', 'string', Rule::in(AuditLog::RESULTS)],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);
        $query = AuditLog::query()->with('actor:id,name,username')->orderByDesc('created_at')->orderByDesc('id');
        $search = trim($filters['q'] ?? '');
        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';
                foreach (['action', 'result', 'subject_type', 'subject_id'] as $field) {
                    $query->orWhereRaw($field." LIKE ? ESCAPE '!'", [$pattern]);
                }
                $query->orWhereHas('actor', function (Builder $actor) use ($pattern): void {
                    $actor->whereRaw("name LIKE ? ESCAPE '!'", [$pattern])->orWhereRaw("username LIKE ? ESCAPE '!'", [$pattern]);
                });
            });
        }
        foreach (['action', 'result'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from'].' 00:00:00');
        }
        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<', CarbonImmutable::parse($filters['date_to'])->addDay()->startOfDay());
        }

        $auditLogs = $query->paginate((int) ($filters['per_page'] ?? 25))->withQueryString();
        $auditLogs->getCollection()->transform(fn (AuditLog $log): AuditLog => $this->present($log));

        return [
            'auditLogs' => $auditLogs,
            'auditFilters' => $filters,
            'auditActions' => $this->actionLabels(),
            'auditResults' => ['success' => 'Berhasil', 'failure' => 'Gagal', 'denied' => 'Ditolak'],
        ];
    }

    /** @return array<string, string> */
    private function actionLabels(): array
    {
        return [
            'user.created' => 'Buat akun pengguna',
            'user.status_changed' => 'Ubah status akun',
            'user.deleted' => 'Hapus akun pengguna',
            'user.pic_verified' => 'Verifikasi PIC',
            'user.password_reset' => 'Reset akses akun',
            'cpcl.created' => 'Tambah data CPCL',
            'cpcl.import_previewed' => 'Pratinjau impor CPCL',
            'cpcl.import_confirmed' => 'Konfirmasi impor CPCL',
            'cpcl.import_cancelled' => 'Batalkan impor CPCL',
            'auth.login' => 'Login',
            'auth.logout' => 'Logout',
            'report.submitted' => 'Kirim laporan',
            'report.verified' => 'Verifikasi laporan',
        ];
    }

    private function present(AuditLog $log): AuditLog
    {
        $actions = $this->actionLabels();
        $results = ['success' => 'Berhasil', 'failure' => 'Gagal', 'denied' => 'Ditolak'];
        $metadata = $log->metadata ?? [];
        $fallbackActor = $log->actor_id
            ? 'Pengguna dihapus'
            : ($log->action === 'auth.login' && $log->result === 'failure' ? 'Pengguna tidak dikenal' : 'Sistem');
        $log->setAttribute('display_actor', $log->actor?->name ?? $fallbackActor);
        $log->setAttribute('display_action', $actions[$log->action] ?? 'Aktivitas administrasi');
        $log->setAttribute('display_result', $results[$log->result] ?? 'Tidak diketahui');

        $prefix = match ($log->subject_type) {
            'user' => 'AK-',
            'cpcl' => 'CPCL-',
            'import_batch' => 'IMP-',
            'report' => 'LP-',
            default => null,
        };
        $object = $prefix && ctype_digit((string) $log->subject_id)
            ? $prefix.str_pad((string) $log->subject_id, 3, '0', STR_PAD_LEFT)
            : ($log->subject_type ? ucfirst(str_replace('_', ' ', $log->subject_type)).' · '.$log->subject_id : '—');
        $log->setAttribute('display_object', $object);
        $log->setAttribute('display_details', $this->details($log, $metadata));

        return $log;
    }

    /** @param array<string, mixed> $metadata */
    private function details(AuditLog $log, array $metadata): string
    {
        return match ($log->action) {
            'user.created' => 'Akun baru dibuat dalam status Menunggu aktivasi; pengguna wajib mengganti password awal.',
            'user.pic_verified' => 'Verifikasi PIC dicatat; status aktivasi akun tidak berubah.',
            'user.password_reset' => 'Password awal diatur ulang; pengguna wajib mengganti password saat login berikutnya.',
            'user.status_changed' => isset($metadata['is_active']) && is_bool($metadata['is_active'])
                ? ($metadata['is_active'] ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.')
                : 'Status aktivasi akun diperbarui.',
            'user.deleted' => 'Akun dihapus dari daftar aktif; riwayat terkait tetap dipertahankan.',
            'cpcl.created' => 'Data CPCL baru ditambahkan.',
            'cpcl.import_previewed' => 'Pratinjau impor dibuat'.(isset($metadata['count']) ? ' · '.(int) $metadata['count'].' baris.' : '.'),
            'cpcl.import_confirmed' => 'Impor selesai'.(isset($metadata['created_count']) ? ' · '.(int) $metadata['created_count'].' ditambahkan' : '').(isset($metadata['skipped_count']) ? ' · '.(int) $metadata['skipped_count'].' dilewati.' : '.'),
            'cpcl.import_cancelled' => 'Batch impor CPCL dibatalkan.',
            'auth.login' => $log->result === 'success' ? 'Login berhasil.' : 'Percobaan login gagal.',
            'auth.logout' => 'Sesi pengguna diakhiri.',
            'report.submitted' => 'Laporan dikirim untuk proses verifikasi.',
            'report.verified' => 'Laporan diperiksa oleh Verifikator.',
            default => 'Aktivitas administrasi tercatat.',
        };
    }
}
