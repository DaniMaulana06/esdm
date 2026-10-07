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

        /*
         * Mulai dari awal bulan sampai kemarin.
         *
         * Sabtu dan Minggu tidak diperiksa.
         */
        $tanggal = $hariIni->copy()->startOfMonth();

        $bkus = Bku::query()
            ->with([
                'bkuKontraks.kontrak',
            ])
            ->get();

        while ($tanggal->lt($hariIni)) {

            // Sabtu dan Minggu dilewati
            if ($tanggal->isWeekday()) {

                foreach ($bkus as $bku) {

                    /*
                     * Kalau BKU tidak memiliki kontrak,
                     * tidak perlu dianggap sebagai laporan yang hilang.
                     */
                    if ($bku->bkuKontraks->isEmpty()) {
                        continue;
                    }

                    $kontrakIds = $bku->bkuKontraks
                        ->pluck('id');

                    /*
                     * Ambil BKU Kontrak yang SUDAH memiliki
                     * laporan pada tanggal tersebut.
                     */
                    $sudahMelaporIds = LaporanHarian::query()
                        ->whereIn('bku_kontrak_id', $kontrakIds)
                        ->whereDate('tanggal', $tanggal)
                        ->pluck('bku_kontrak_id');

                    /*
                     * Cari kontrak yang BELUM memiliki laporan.
                     */
                    $belumMelapor = $bku->bkuKontraks
                        ->whereNotIn('id', $sudahMelaporIds);

                    if ($belumMelapor->isNotEmpty()) {

                        $hasil[] = [
                            'tanggal' => $tanggal->toDateString(),
                            'bku_id' => $bku->id,
                            'bku' => $bku->nama,
                            'missing_count' => $belumMelapor->count(),
                            'contracts' => $belumMelapor
                                ->map(fn ($bkuKontrak) => [
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

        $hariIni = Carbon::today();

        // Mulai dari awal bulan sampai kemarin
        $tanggal = $hariIni->copy()->startOfMonth();

        $bku = Bku::with(['bkuKontraks.kontrak'])
            ->find($bkuId);

        if (! $bku || $bku->bkuKontraks->isEmpty()) {
            return [];
        }

        $kontrakIds = $bku->bkuKontraks->pluck('id');

        while ($tanggal->lt($hariIni)) {
            if ($tanggal->isWeekday()) {
                $sudahMelaporIds = LaporanHarian::query()
                    ->whereIn('bku_kontrak_id', $kontrakIds)
                    ->whereDate('tanggal', $tanggal)
                    ->pluck('bku_kontrak_id');

                $belumMelapor = $bku->bkuKontraks
                    ->whereNotIn('id', $sudahMelaporIds);

                if ($belumMelapor->isNotEmpty()) {
                    $hasil[] = [
                        'tanggal'       => $tanggal->toDateString(),
                        'missing_count' => $belumMelapor->count(),
                        'contracts'     => $belumMelapor
                            ->map(fn ($bkuKontrak) => [
                                'id'   => $bkuKontrak->id,
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