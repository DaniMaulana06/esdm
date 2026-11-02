<?php

namespace App\Services;

use App\Models\Bku;
use App\Models\LaporanHarian;
use Carbon\Carbon;

class LaporanMonitoringService
{
    public function getBkuBelumLapor(): array
    {
        $hasil = [];

        $hariIni = Carbon::today();

        $bkus = Bku::query()
            ->with([
                'bkuKontraks.kontrak',
            ])
            ->get();

        /*
         * Batas maksimal lookback adalah 6 bulan ke belakang.
         * Filter created_at di dalam loop sudah menangani kontrak yang
         * baru dibuat setelah tanggal ini, sehingga setiap BKU secara
         * otomatis hanya diperiksa sejak kontraknya aktif.
         */
        $tanggal = $hariIni->copy()->subMonths(6)->startOfMonth();

        /*
         * Pre-load semua laporan dalam rentang tanggal sekaligus
         * untuk menghindari N+1 query di dalam loop.
         */
        $sudahMelaporMap = [];
        LaporanHarian::query()
            ->whereDate('tanggal', '>=', $tanggal)
            ->whereDate('tanggal', '<', $hariIni)
            ->get(['bku_kontrak_id', 'tanggal'])
            ->each(function ($laporan) use (&$sudahMelaporMap) {
                $tgl = Carbon::parse($laporan->tanggal)->toDateString();
                $sudahMelaporMap[$tgl][] = $laporan->bku_kontrak_id;
            });

        while ($tanggal->lt($hariIni)) {

            // Sabtu dan Minggu dilewati
            if ($tanggal->isWeekday()) {

                $sudahMelaporPadaTanggal = $sudahMelaporMap[$tanggal->toDateString()] ?? [];

                foreach ($bkus as $bku) {

                    /*
                     * Kalau BKU tidak memiliki kontrak,
                     * tidak perlu dianggap sebagai laporan yang hilang.
                     */
                    if ($bku->bkuKontraks->isEmpty()) {
                        continue;
                    }

                    $kontrakIds = $bku->bkuKontraks->pluck('id');

                    $sudahMelaporIds = array_values(
                        array_intersect($sudahMelaporPadaTanggal, $kontrakIds->all())
                    );

                    /*
                     * Cari kontrak yang BELUM memiliki laporan.
                     */
                    $belumMelapor = $bku->bkuKontraks
                        ->whereNotIn('id', $sudahMelaporIds)
                        ->filter(function ($bkuKontrak) use ($tanggal) {
                            return $bkuKontrak->created_at
                                ? $bkuKontrak->created_at->startOfDay()->lt($tanggal)
                                : true;
                        });

                    if ($belumMelapor->isNotEmpty()) {

                        $hasil[] = [
                            'tanggal' => $tanggal->toDateString(),
                            'bku_id' => $bku->id,
                            'bku' => $bku->nama,
                            'missing_count' => $belumMelapor->count(),
                            'contracts' => $belumMelapor
                                ->map(fn($bkuKontrak) => [
                                    'id' => $bkuKontrak->id,
                                    'nama' => $bkuKontrak->kontrak->nama,
                                ])
                                ->values()
                                ->all(),
                        ];
                    }
                }
            }

            $tanggal->addDay();
        }

        return $hasil;
    }

    /**
     * Ambil daftar tanggal yang terlewat (belum ada laporan) untuk BKU tertentu.
     * Digunakan di dashboard operator BKU agar dapat mengisi laporan yang tertinggal.
     */
    public function getMissingDatesForBku(int $bkuId): array
    {
        $hasil = [];

        $bku = Bku::with(['bkuKontraks.kontrak'])
            ->find($bkuId);

        if (!$bku || $bku->bkuKontraks->isEmpty()) {
            return [];
        }

        $kontrakIds = $bku->bkuKontraks->pluck('id');

        $hariIni = Carbon::today();

        /*
         * Titik mulai pengecekan: tanggal kontrak terlama milik BKU ini,
         * dibatasi maksimal 6 bulan ke belakang dari hari ini.
         * Artinya: jika kontrak dibuat 2 bulan lalu, cek dari saat kontrak dibuat.
         * Jika kontrak sudah ada sejak setahun lalu, cek maksimal 6 bulan ke belakang.
         */
        $batasanMaksimal = $hariIni->copy()->subMonths(6)->startOfMonth();
        $tanggalKontrakTerlama = $bku->bkuKontraks->min('created_at');

        if ($tanggalKontrakTerlama) {
            $tanggalMulaiKontrak = Carbon::parse($tanggalKontrakTerlama)->startOfDay();
            // Ambil yang lebih baru: tanggal kontrak dibuat vs batas 6 bulan
            $tanggal = $tanggalMulaiKontrak->gt($batasanMaksimal)
                ? $tanggalMulaiKontrak
                : $batasanMaksimal;
        } else {
            $tanggal = $batasanMaksimal;
        }

        // Ambil data laporan harian yang sudah ada dalam rentang tanggal untuk mengoptimalkan performa (menghindari query di dalam loop)
        $sudahMelaporMap = [];
        LaporanHarian::query()
            ->whereIn('bku_kontrak_id', $kontrakIds)
            ->whereDate('tanggal', '>=', $tanggal)
            ->whereDate('tanggal', '<', $hariIni)
            ->get(['bku_kontrak_id', 'tanggal'])
            ->each(function ($laporan) use (&$sudahMelaporMap) {
                $tgl = Carbon::parse($laporan->tanggal)->toDateString();
                $sudahMelaporMap[$tgl][] = $laporan->bku_kontrak_id;
            });

        while ($tanggal->lt($hariIni)) {
            if ($tanggal->isWeekday()) {
                $sudahMelaporIds = $sudahMelaporMap[$tanggal->toDateString()] ?? [];

                $belumMelapor = $bku->bkuKontraks
                    ->whereNotIn('id', $sudahMelaporIds)
                    ->filter(function ($bkuKontrak) use ($tanggal) {
                        return $bkuKontrak->created_at
                            ? $bkuKontrak->created_at->startOfDay()->lt($tanggal)
                            : true;
                    });

                if ($belumMelapor->isNotEmpty()) {
                    $hasil[] = [
                        'tanggal' => $tanggal->toDateString(),
                        'missing_count' => $belumMelapor->count(),
                        'contracts' => $belumMelapor
                            ->map(fn($bkuKontrak) => [
                                'id' => $bkuKontrak->id,
                                'nama' => $bkuKontrak->kontrak->nama,
                            ])
                            ->values()
                            ->all(),
                    ];
                }
            }

            $tanggal->addDay();
        }

        return $hasil;
    }
}