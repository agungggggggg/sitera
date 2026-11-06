<?php

namespace Database\Seeders;

use App\Models\CpclRecord;
use App\Models\Report;
use App\Models\ReportVerification;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class SearchFilterDemoSeeder extends Seeder
{
    public function run(): void
    {
        $isIsolatedTest = app()->environment('testing')
            && config('database.default') === 'sqlite'
            && config('database.connections.sqlite.database') === ':memory:';
        if (! $isIsolatedTest && ! config('demo.approved_target', false)) {
            throw new RuntimeException('Demo seeding is disabled outside the approved local database target.');
        }
        foreach (['users', 'cpcl_records', 'reports', 'report_verifications', 'audit_logs'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException('Database schema is incomplete; refusing to seed.');
            }
        }
        if (User::withTrashed()->where('email', 'like', 'filter-demo-%@example.test')->exists()
            || CpclRecord::where('no', 'like', 'FILTER-DEMO-%')->exists()
            || Report::where('nomor_tiket', 'like', 'FILTER-DEMO-%')->exists()) {
            throw new RuntimeException('FILTER-DEMO data already exists; nothing was changed.');
        }
        $this->assertRegionAvailability();

        $temporaryPassword = bin2hex(random_bytes(12));
        $passwordHash = Hash::make($temporaryPassword);
        $created = DB::transaction(function () use ($passwordHash, $temporaryPassword): array {
            $roleReporter = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
            $roleVerifier = Role::firstOrCreate(['code' => 'verifikator_pusat'], ['name' => 'Verifikator Pusat']);
            $admin = User::query()->whereHas('role', fn ($query) => $query->where('code', 'super_admin'))
                ->where('is_active', true)->where('must_change_password', false)->orderBy('id')->firstOrFail();

            $west = $this->createUser('filter-demo-barat', 'FILTER-DEMO Pelapor Barat', $roleReporter->id, $passwordHash, 'Jawa Barat', 'Subang');
            $east = $this->createUser('filter-demo-timur', 'FILTER-DEMO Pelapor Timur', $roleReporter->id, $passwordHash, 'Jawa Timur', 'Kabupaten Malang');
            $verifier = $this->createUser('filter-demo-verifikator', 'FILTER-DEMO Verifikator', $roleVerifier->id, $passwordHash);

            $cpclRows = [];
            foreach ([
                ['Jawa Barat', 'Subang', 'Pamanukan', 'Mulyasari', 'FILTER-DEMO P3A Tirta Barat', 'FILTER-DEMO DI Pantura'],
                ['Jawa Barat', 'Subang', 'Pamanukan', 'Sukamandi', 'FILTER-DEMO P3A Sawah', 'FILTER-DEMO DI Pantura'],
                ['Jawa Timur', 'Kabupaten Malang', 'Daik', 'Dabo', 'FILTER-DEMO P3A Selatan', 'FILTER-DEMO DI Brantas'],
            ] as $index => [$province, $regency, $district, $village, $group, $irrigation]) {
                $cpclRows[] = CpclRecord::create([
                    'no' => 'FILTER-DEMO-'.($index + 1), 'nama_di' => $irrigation,
                    'provinsi' => $province, 'kabupaten' => $regency, 'kecamatan' => $district, 'desa' => $village,
                    'kel_tani' => $group, 'nama_ketua' => 'FILTER-DEMO Ketua '.($index + 1),
                    'aktif' => true, 'created_by' => $admin->id,
                ]);
            }

            $fixtures = [
                [$west, $cpclRows[0], 'DRAF', null, null],
                [$west, $cpclRows[0], 'TERKIRIM', 'SESUAI', '2026-11-02 09:00:00'],
                [$west, $cpclRows[1], 'TERKIRIM', 'TIDAK_SESUAI', '2026-11-03 09:00:00'],
                [$west, null, 'TERKIRIM', null, '2026-11-04 09:00:00'],
                [$east, $cpclRows[2], 'TERKIRIM', null, '2026-11-05 09:00:00'],
            ];
            $reports = [];
            foreach ($fixtures as $index => [$owner, $cpcl, $status, $result, $sentAt]) {
                $report = Report::create([
                    'user_id' => $owner->id, 'cpcl_id' => $cpcl?->id,
                    'nomor_tiket' => 'FILTER-DEMO-LP-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'status' => $status, 'tanggal_kejadian' => '2026-10-'.str_pad((string) (20 + $index), 2, '0', STR_PAD_LEFT),
                    'jenis_kerusakan' => 'FILTER-DEMO Kerusakan '.($index + 1),
                    'deskripsi' => 'FILTER-DEMO deskripsi laporan '.($index + 1),
                    'nama_saluran' => $cpcl?->nama_di ?? 'FILTER-DEMO Saluran fallback',
                    'provinsi' => $owner->provinsi, 'kabupaten' => $owner->kabupaten,
                    'kecamatan' => $cpcl?->kecamatan ?? 'Pamanukan', 'desa' => $cpcl?->desa ?? 'Mulyasari',
                    'dikirim_pada' => $sentAt,
                ]);
                $reports[] = $report;
                if ($result !== null) {
                    ReportVerification::create([
                        'report_id' => $report->id, 'verifier_id' => $verifier->id,
                        'hasil' => $result, 'catatan' => $result === 'TIDAK_SESUAI' ? 'FILTER-DEMO Catatan tindak lanjut' : null,
                        'diperiksa_pada' => $sentAt,
                    ]);
                }
            }
            $audit = app(AuditLogger::class);
            $audit->log($admin, 'user.created', 'success', 'user', $west->id);
            $audit->log($admin, 'cpcl.created', 'success', 'cpcl', $cpclRows[0]->id);
            $audit->log($verifier, 'report.verified', 'success', 'report', $reports[1]->id);
            $audit->log($admin, 'user.status_changed', 'denied', 'user', $east->id);

            return compact('west', 'east', 'verifier', 'temporaryPassword');
        });

        $this->command?->info('Seeded FILTER-DEMO data: 3 users, 3 CPCL records, 5 reports, 4 audit events. Existing accounts were not modified.');
        $this->command?->warn('Temporary credentials are printed only because the operator explicitly requested them.');
        $this->command?->line('Pelapor Barat: '.$created['west']->username.' / '.$created['temporaryPassword']);
        $this->command?->line('Pelapor Timur: '.$created['east']->username.' / '.$created['temporaryPassword']);
        $this->command?->line('Verifikator: '.$created['verifier']->username.' / '.$created['temporaryPassword']);
        $this->command?->line('Pimpinan & Super Admin: gunakan akun yang sudah ada (tidak dibuat/diubah).');
    }

    private function createUser(string $username, string $name, int $roleId, string $passwordHash, ?string $province = null, ?string $regency = null): User
    {
        return User::create([
            'username' => $username, 'name' => $name,
            'email' => $username.'@example.test', 'password' => $passwordHash,
            'role_id' => $roleId, 'provinsi' => $province, 'kabupaten' => $regency,
            'is_active' => true, 'must_change_password' => false,
        ]);
    }

    private function assertRegionAvailability(): void
    {
        if (User::withTrashed()->whereHas('role', fn ($query) => $query->where('code', 'pelapor_daerah'))
            ->where('provinsi', 'Jawa Barat')->where('kabupaten', 'Subang')->exists()
            || User::withTrashed()->whereHas('role', fn ($query) => $query->where('code', 'pelapor_daerah'))
                ->where('provinsi', 'Jawa Timur')->where('kabupaten', 'Kabupaten Malang')->exists()) {
            throw new RuntimeException('A Pelapor already occupies a demo region; refusing to violate one-Pelapor-per-region policy.');
        }
    }
}
