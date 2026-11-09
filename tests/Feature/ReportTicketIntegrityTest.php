<?php

namespace Tests\Feature;

use App\Models\CpclRecord;
use App\Models\Report;
use App\Models\ReportVerification;
use App\Models\Role;
use App\Models\User;
use App\Services\RegionCatalog;
use App\Services\ReportTicketGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportTicketIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_cold_start_allocates_sequence_one(): void
    {
        $generator = app(ReportTicketGenerator::class);
        $date = '2026-12-15';

        $report = $generator->generate($date, function (string $ticket) use ($date): Report {
            return Report::create([
                'user_id' => $this->pelapor()->id,
                'nomor_tiket' => $ticket,
                'status' => 'DRAF',
                'tanggal_kejadian' => $date,
                'provinsi' => 'Jawa Barat',
                'kabupaten' => 'Subang',
                'jenis_kerusakan' => 'Saluran Sekunder',
                'deskripsi' => 'Cold start test.',
                'sumber_isian' => 'MANUAL',
            ]);
        });

        $this->assertSame('LP-20261215-0001', $report->nomor_tiket);
    }

    public function test_gap_in_sequence_does_not_cause_duplicate_collision(): void
    {
        $generator = app(ReportTicketGenerator::class);
        $user = $this->pelapor();
        $date = '2026-11-20';

        // Pre-create ticket 0001 and 0003 (ticket 0002 was deleted, creating a gap)
        Report::create([
            'user_id' => $user->id,
            'nomor_tiket' => 'LP-20261120-0001',
            'status' => 'DRAF',
            'tanggal_kejadian' => $date,
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Ticket 1',
            'sumber_isian' => 'MANUAL',
        ]);
        Report::create([
            'user_id' => $user->id,
            'nomor_tiket' => 'LP-20261120-0003',
            'status' => 'DRAF',
            'tanggal_kejadian' => $date,
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Ticket 3',
            'sumber_isian' => 'MANUAL',
        ]);

        // Total count of reports is 2. If COUNT() + 1 was used, it would yield 0003 (crash!).
        // But with highest sequence + 1, it must yield 0004!
        $newReport = $generator->generate($date, function (string $ticket) use ($user, $date): Report {
            return Report::create([
                'user_id' => $user->id,
                'nomor_tiket' => $ticket,
                'status' => 'DRAF',
                'tanggal_kejadian' => $date,
                'provinsi' => 'Jawa Barat',
                'kabupaten' => 'Subang',
                'jenis_kerusakan' => 'Saluran Sekunder',
                'deskripsi' => 'Ticket 4',
                'sumber_isian' => 'MANUAL',
            ]);
        });

        $this->assertSame('LP-20261120-0004', $newReport->nomor_tiket);
    }

    public function test_all_three_generators_coordinate_on_the_same_sequence_without_collision(): void
    {
        Carbon::setTestNow('2026-11-06 10:00:00');
        $pelapor = $this->pelapor();
        $today = '2026-11-06';
        $dateFormatted = '20261106';

        // 1. Path 1: Manual Report (ReportController::store)
        $this->actingAs($pelapor)->post('/laporan', [
            'tanggal_kejadian' => $today,
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan',
            'desa' => 'Mulyasari',
            'nama_saluran' => 'Saluran Sekunder',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan manual ke-1.',
        ])->assertRedirect('/laporan');

        $report1 = Report::where('nomor_tiket', 'like', 'LP-'.$dateFormatted.'-%')->orderByDesc('id')->firstOrFail();
        $this->assertSame('LP-'.$dateFormatted.'-0001', $report1->nomor_tiket);

        // 2. Path 2: Package Report (ReportPackageController::store)
        $cpcl = CpclRecord::create([
            'no' => 'CPCL-INT-1',
            'nama_di' => 'DI Ciasem',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'kecamatan' => 'Pamanukan',
            'desa' => 'Mulyasari',
            'kel_tani' => 'P3A Mandiri',
            'aktif' => true,
        ]);

        $this->actingAs($pelapor)->post('/laporan/buat/'.$cpcl->id, [
            '_action' => 'draft',
            'bast' => UploadedFile::fake()->create('bast.pdf', 100, 'application/pdf'),
            'lpj' => UploadedFile::fake()->create('lpj.pdf', 100, 'application/pdf'),
            'dokumentasi' => UploadedFile::fake()->create('doc.pptx', 100, 'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
        ])->assertRedirect('/laporan');

        $report2 = Report::where('nomor_tiket', 'like', 'LP-'.$dateFormatted.'-%')->orderByDesc('id')->firstOrFail();
        $this->assertSame('LP-'.$dateFormatted.'-0002', $report2->nomor_tiket);

        // Mark report1 as submitted and verified TIDAK_SESUAI so it can be corrected
        $report1->forceFill(['status' => 'TERKIRIM', 'dikirim_pada' => now()])->save();
        $verifier = $this->verifier();
        ReportVerification::create([
            'report_id' => $report1->id,
            'verifier_id' => $verifier->id,
            'hasil' => 'TIDAK_SESUAI',
            'catatan' => 'Perbaiki data.',
            'diperiksa_pada' => now(),
        ]);

        // 3. Path 3: Correction Report (ReportCorrectionController::create)
        $this->actingAs($pelapor)->post('/laporan/'.$report1->id.'/koreksi')
            ->assertRedirectContains('/koreksi/edit');

        $correction = Report::where('koreksi_dari', $report1->id)->firstOrFail();
        $this->assertSame('LP-'.$dateFormatted.'-0003', $correction->nomor_tiket);
    }

    public function test_correction_creation_is_idempotent_and_does_not_waste_or_collide_tickets(): void
    {
        $pelapor = $this->pelapor();
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $original = Report::create([
            'user_id' => $pelapor->id,
            'nomor_tiket' => 'LP-'.Carbon::now('Asia/Jakarta')->format('Ymd').'-0010',
            'status' => 'TERKIRIM',
            'tanggal_kejadian' => $today,
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
            'jenis_kerusakan' => 'Saluran Sekunder',
            'deskripsi' => 'Laporan awal',
            'sumber_isian' => 'MANUAL',
            'dikirim_pada' => now(),
        ]);

        ReportVerification::create([
            'report_id' => $original->id,
            'verifier_id' => $this->verifier()->id,
            'hasil' => 'TIDAK_SESUAI',
            'catatan' => 'Perlu revisi.',
            'diperiksa_pada' => now(),
        ]);

        // First call creates the correction
        $this->actingAs($pelapor)->post('/laporan/'.$original->id.'/koreksi')
            ->assertRedirectContains('/koreksi/edit');

        $firstTicket = Report::where('koreksi_dari', $original->id)->value('nomor_tiket');
        $totalReportsAfterFirst = Report::count();

        // Second call (simulating repeat or concurrent request) must return the existing correction draft
        $this->actingAs($pelapor)->post('/laporan/'.$original->id.'/koreksi')
            ->assertRedirectContains('/koreksi/edit');

        $this->assertSame($totalReportsAfterFirst, Report::count());
        $this->assertSame($firstTicket, Report::where('koreksi_dari', $original->id)->value('nomor_tiket'));
    }

    public function test_maria_db_named_lock_advisory_mutex_if_mysql_configured(): void
    {
        $mysqlDriver = config('database.connections.mysql.driver');
        $this->assertSame('mysql', $mysqlDriver);

        // Under PHPUnit, DB_DATABASE env is overridden with ':memory:', so restore the real MariaDB database for the mysql connection
        config(['database.connections.mysql.database' => 'sistem_laporan_irigasi']);
        DB::purge('mysql');

        $lockKey = 'sitera_test_mutex_'.bin2hex(random_bytes(4));
        $res = DB::connection('mysql')->selectOne('SELECT GET_LOCK(?, 2) as acquired', [$lockKey]);
        $this->assertNotNull($res);
        $this->assertSame(1, (int) $res->acquired);

        $released = DB::connection('mysql')->selectOne('SELECT RELEASE_LOCK(?) as freed', [$lockKey]);
        $this->assertSame(1, (int) $released->freed);
    }

    private function pelapor(): User
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        return User::factory()->create([
            'role_id' => $role->id,
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Subang',
        ]);
    }

    private function verifier(): User
    {
        $role = Role::firstOrCreate(['code' => 'verifikator_pusat'], ['name' => 'Verifikator Pusat']);
        return User::factory()->create(['role_id' => $role->id]);
    }
}
