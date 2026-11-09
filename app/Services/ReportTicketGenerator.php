<?php

namespace App\Services;

use App\Models\Report;
use Carbon\CarbonInterface;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ReportTicketGenerator
{
    private const LOCK_TIMEOUT_SECONDS = 10;

    /**
     * Allocate the next ticket number and execute the creation callback within a locked transaction.
     *
     * @template T
     * @param  CarbonInterface|string  $date
     * @param  callable(string): T  $creator
     * @return T
     */
    public function generate(CarbonInterface|string $date, callable $creator, ?string $connectionName = null): mixed
    {
        $parsedDate = $date instanceof CarbonInterface ? $date : Carbon::parse($date);
        $dateFormatted = $parsedDate->format('Ymd');
        $prefix = 'LP-'.$dateFormatted.'-';
        $lockKey = 'sitera_ticket_lock_LP_'.$dateFormatted;

        $connection = DB::connection($connectionName);
        $isMysql = in_array($connection->getDriverName(), ['mysql', 'mariadb'], true);

        $lockAcquired = false;

        try {
            if ($isMysql) {
                $statement = $connection->selectOne('SELECT GET_LOCK(?, ?) as acquired', [
                    $lockKey,
                    self::LOCK_TIMEOUT_SECONDS,
                ]);

                $lockAcquired = ((int) ($statement?->acquired ?? 0)) === 1;

                if (! $lockAcquired) {
                    throw new RuntimeException('Gagal memperoleh lock alokasi nomor tiket untuk tanggal '.$dateFormatted.'. Silakan coba beberapa saat lagi.');
                }
            }

            return $connection->transaction(function () use ($prefix, $creator, $connection): mixed {
                $last = Report::on($connection->getName())
                    ->where('nomor_tiket', 'like', $prefix.'%')
                    ->lockForUpdate()
                    ->orderByDesc('nomor_tiket')
                    ->value('nomor_tiket');

                $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;
                $ticket = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

                return $creator($ticket);
            });
        } finally {
            if ($isMysql && $lockAcquired) {
                try {
                    $connection->selectOne('SELECT RELEASE_LOCK(?) as released', [$lockKey]);
                } catch (Throwable) {
                    // Lock will be automatically freed when connection closes if release fails.
                }
            }
        }
    }
}
