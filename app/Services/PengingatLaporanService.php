<?php

namespace App\Services;

use App\Mail\PengingatLaporanHarianMail;
use App\Models\Bku;
use App\Models\LaporanHarian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PengingatLaporanService
{
    /**
     * Cari seluruh BKU yang memiliki tanggal laporan tertunggak.
     *
     * @param int $daysBack Jumlah hari kalender ke belakang yang diperiksa (sebelum hari ini)
     * @param int|null $bkuId Filter untuk BKU tertentu (opsional)
     * @return array<int, array{bku: Bku, missing_dates: array<int, string>, operators: Collection<int, User>, verified_operators: Collection<int, User>}>
     */
    public function findMissingReports(int $daysBack = 30, ?int $bkuId = null): array
    {
        $today = Carbon::today();
        
        // Aturan: Pemeriksaan dilakukan setelah tanggal pelaporan berakhir.
        // Hari ini tidak diperiksa karena hari pelaporan masih berjalan.
        $endDate = $today->copy()->subDay()->endOfDay();
        $startDate = $today->copy()->subDays($daysBack)->startOfDay();

        $bkuQuery = Bku::query()
            ->with([
                'bkuKontraks.kontrak',
                'users' => fn($query) => $query->where('role', 'operator_bku'),
            ]);

        if ($bkuId !== null) {
            $bkuQuery->where('id', $bkuId);
        }

        $bkus = $bkuQuery->orderBy('nama')->get();
        $results = [];

        foreach ($bkus as $bku) {
            if ($bku->bkuKontraks->isEmpty()) {
                continue;
            }

            $kontrakIds = $bku->bkuKontraks->pluck('id');

            // Ambil semua laporan harian milik kontrak BKU ini dalam rentang tanggal
            $existingReports = LaporanHarian::query()
                ->whereIn('bku_kontrak_id', $kontrakIds)
                ->whereDate('tanggal', '>=', $startDate->toDateString())
                ->whereDate('tanggal', '<=', $endDate->toDateString())
                ->select(['bku_kontrak_id', 'tanggal'])
                ->get()
                ->groupBy(function ($report) {
                    return Carbon::parse($report->tanggal)->toDateString();
                });


            $missingDates = [];
            $currentDate = $startDate->copy()->startOfDay();

            // Periksa setiap hari kerja (Sabtu dan Minggu dilewati / tidak dihitung)
            while ($currentDate->lte($endDate)) {
                if ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }

                $dateStr = $currentDate->toDateString();

                // Kontrak yang aktif dan wajib melapor: dibuat sebelum tanggal pelaporan
                $activeContracts = $bku->bkuKontraks->filter(function ($bkuKontrak) use ($currentDate) {
                    if (! $bkuKontrak->created_at) {
                        return true;
                    }
                    return $bkuKontrak->created_at->startOfDay()->lt($currentDate);
                });

                // Jika BKU belum memiliki kontrak aktif pada tanggal ini, lewati
                if ($activeContracts->isNotEmpty()) {
                    $reportedKontrakIds = $existingReports->get($dateStr, collect())
                        ->pluck('bku_kontrak_id')
                        ->unique();

                    // Jika jumlah kontrak yang sudah melapor kurang dari total kontrak aktif
                    if ($reportedKontrakIds->count() < $activeContracts->count()) {
                        $missingDates[] = $dateStr;
                    }
                }

                $currentDate->addDay();
            }


            if (! empty($missingDates)) {
                $operators = $bku->users->where('role', 'operator_bku');
                $verifiedOperators = $operators->filter(function (User $user) {
                    return ! empty($user->email) && $user->email_verified_at !== null;
                });

                $results[$bku->id] = [
                    'bku' => $bku,
                    'missing_dates' => $missingDates,
                    'operators' => $operators->values(),
                    'verified_operators' => $verifiedOperators->values(),
                ];
            }
        }

        return $results;
    }

    /**
     * Eksekusi pemeriksaan dan pengiriman email pengingat.
     *
     * @param bool $dryRun Jika true, hanya simulasi tanpa kirim email dan tanpa tulis cache
     * @param int $daysBack Jumlah hari ke belakang yang diperiksa
     * @param int|null $bkuId ID BKU tertentu
     * @return array{
     *     total_bku_examined: int,
     *     total_missing_bku: int,
     *     total_emails_sent: int,
     *     total_skipped_idempotent: int,
     *     total_skipped_no_operator: int,
     *     total_failed: int,
     *     details: array<int, mixed>
     * }
     */
    public function prosesPengingat(bool $dryRun = false, int $daysBack = 30, ?int $bkuId = null): array
    {
        $missingReports = $this->findMissingReports($daysBack, $bkuId);
        $today = Carbon::today()->toDateString();

        $stats = [
            'total_bku_examined' => Bku::count(),
            'total_missing_bku' => count($missingReports),
            'total_emails_sent' => 0,
            'total_skipped_idempotent' => 0,
            'total_skipped_no_operator' => 0,
            'total_failed' => 0,
            'details' => [],
        ];

        foreach ($missingReports as $item) {
            /** @var Bku $bku */
            $bku = $item['bku'];
            $missingDates = $item['missing_dates'];
            $verifiedOperators = $item['verified_operators'];

            // 1. Validasi keberadaan operator terverifikasi
            if ($verifiedOperators->isEmpty()) {
                $stats['total_skipped_no_operator']++;
                $stats['details'][] = [
                    'bku_id' => $bku->id,
                    'bku_nama' => $bku->nama,
                    'status' => 'skipped_no_verified_operator',
                    'missing_dates' => $missingDates,
                    'reason' => 'Tidak ada akun operator_bku dengan email terverifikasi.',
                ];

                Log::warning("[SUMAS Pengingat] BKU {$bku->nama} (ID: {$bku->id}) memiliki " . count($missingDates) . " tanggal laporan tertunggak, namun tidak memiliki operator_bku dengan email terverifikasi.");
                continue;
            }

            // 2. Cek Idempotensi harian per BKU dan tanggal pengiriman
            $idempotencyKey = $this->getIdempotencyKey($bku->id, $today);
            if (Cache::has($idempotencyKey)) {
                $stats['total_skipped_idempotent']++;
                $stats['details'][] = [
                    'bku_id' => $bku->id,
                    'bku_nama' => $bku->nama,
                    'status' => 'skipped_already_sent_today',
                    'missing_dates' => $missingDates,
                    'reason' => "Pengingat untuk BKU ini sudah dikirimkan pada tanggal {$today}.",
                ];

                Log::info("[SUMAS Pengingat] Pengingat BKU {$bku->nama} (ID: {$bku->id}) dilewati karena sudah dikirim hari ini ({$today}).");
                continue;
            }

            // 3. Jika dry run, jangan kirim email dan jangan ubah cache
            if ($dryRun) {
                $stats['details'][] = [
                    'bku_id' => $bku->id,
                    'bku_nama' => $bku->nama,
                    'status' => 'dry_run_ready',
                    'missing_dates' => $missingDates,
                    'recipients' => $verifiedOperators->pluck('email')->all(),
                ];
                continue;
            }

            // 4. Kirim email secara terisolasi per BKU
            try {
                foreach ($verifiedOperators as $operator) {
                    Mail::to($operator->email)->send(new PengingatLaporanHarianMail(
                        user: $operator,
                        bku: $bku,
                        missingDates: $missingDates
                    ));
                    $stats['total_emails_sent']++;
                }

                // 5. Tandai cache idempotensi HANYA setelah pengiriman berhasil
                // TTL 2 hari agar aman hingga pemeriksaan esok hari
                Cache::put($idempotencyKey, [
                    'sent_at' => now()->toIso8601String(),
                    'bku_id' => $bku->id,
                    'missing_dates' => $missingDates,
                ], now()->addDays(2));

                // Simpan juga kunci per tanggal laporan yang terlambat sesuai batasan desain
                foreach ($missingDates as $mDate) {
                    Cache::put(
                        $this->getDateSpecificIdempotencyKey($bku->id, $mDate, $today),
                        now()->toIso8601String(),
                        now()->addDays(2)
                    );
                }

                $stats['details'][] = [
                    'bku_id' => $bku->id,
                    'bku_nama' => $bku->nama,
                    'status' => 'sent',
                    'missing_dates' => $missingDates,
                    'recipients' => $verifiedOperators->pluck('email')->all(),
                ];

                Log::info("[SUMAS Pengingat] Berhasil mengirim email pengingat untuk BKU {$bku->nama} (ID: {$bku->id}) ke " . $verifiedOperators->pluck('email')->implode(', ') . " (" . count($missingDates) . " tanggal).");
            } catch (Throwable $e) {
                $stats['total_failed']++;
                $stats['details'][] = [
                    'bku_id' => $bku->id,
                    'bku_nama' => $bku->nama,
                    'status' => 'failed',
                    'missing_dates' => $missingDates,
                    'error' => $e->getMessage(),
                ];

                Log::error("[SUMAS Pengingat] Gagal mengirim email pengingat BKU {$bku->nama} (ID: {$bku->id}): " . $e->getMessage());
                // Jangan tandai cache jika gagal agar bisa dicoba kembali
            }
        }

        return $stats;
    }

    /**
     * Kunci cache idempotensi harian per BKU.
     */
    public function getIdempotencyKey(int $bkuId, string $dateSent): string
    {
        return "pengingat_laporan:bku:{$bkuId}:sent:{$dateSent}";
    }

    /**
     * Kunci cache spesifik per tanggal laporan yang terlambat dan tanggal pengiriman.
     */
    public function getDateSpecificIdempotencyKey(int $bkuId, string $reportDate, string $dateSent): string
    {
        return "pengingat_laporan:bku:{$bkuId}:tgl:{$reportDate}:sent:{$dateSent}";
    }
}
