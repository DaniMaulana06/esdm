<?php

namespace App\Services;

use App\Models\Bku;
use App\Models\LaporanHarian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Menghitung disiplin pelaporan harian per BKU pada rentang tanggal dashboard.
 *
 * Aturan perhitungan:
 * - Hanya hari kerja (Senin–Jumat) yang dinilai, sama seperti aturan monitoring
 *   laporan yang sudah ada.
 * - Hari ini dan tanggal setelahnya belum dinilai karena laporan hari ini belum
 *   tentu terlambat.
 * - Satu hari dianggap "terlambat" apabila ada laporan untuk tanggal tersebut,
 *   tetapi dibuat (created_at) setelah tanggal laporan. Laporan susulan/backfill
 *   tetap dihitung terlambat, bukan tepat waktu.
 * - Satu hari dianggap "tidak melapor" apabila tidak ada laporan sama sekali
 *   untuk tanggal tersebut.
 * - Hari dihitung efektif hanya jika BKU sudah memiliki kontrak yang aktif
 *   (dibuat sebelum tanggal tersebut), mengikuti aturan di LaporanMonitoringService.
 */
class LaporanTerlambatService
{
    /**
     * @return Collection<int, array{
     *     bku_id: int,
     *     nama: string,
     *     total_hari_efektif: int,
     *     terlambat: int,
     *     tidak_melapor: int,
     *     tepat_waktu: int,
     *     persentase_tepat_waktu: float
     * }>
     */
    public function hitung(
        User $user,
        string $startDate,
        string $endDate,
        ?int $bkuId = null,
        ?int $kontrakId = null,
    ): Collection {
        $mulai = Carbon::parse($startDate)->startOfDay();
        $selesai = Carbon::parse($endDate)->startOfDay();

        // Hari ini belum bisa dinilai: laporan hari ini masih boleh dikirim nanti.
        $batasPenilaian = Carbon::yesterday();

        if ($selesai->gt($batasPenilaian)) {
            $selesai = $batasPenilaian->copy();
        }

        if ($mulai->gt($selesai)) {
            return collect();
        }

        // Ambil BKU sesuai hak akses + filter, beserta kontrak yang relevan.
        $bkuQuery = Bku::query()
            ->select('id', 'nama')
            ->with([
                'bkuKontraks' => function ($query) use ($kontrakId) {
                    $query->select('id', 'bku_id', 'kontrak_id', 'created_at');

                    if ($kontrakId) {
                        $query->where('kontrak_id', $kontrakId);
                    }
                },
            ])
            ->orderBy('nama');

        if ($user->isOperatorBku()) {
            $bkuQuery->where('id', $user->bku_id);
        }

        if ($bkuId) {
            $bkuQuery->where('id', $bkuId);
        }

        $bkus = $bkuQuery->get()
            ->filter(fn (Bku $bku) => $bku->bkuKontraks->isNotEmpty());

        if ($bkus->isEmpty()) {
            return collect();
        }

        $kontrakKeBku = [];
        $kontrakIds = collect();

        foreach ($bkus as $bku) {
            foreach ($bku->bkuKontraks as $bkuKontrak) {
                $kontrakKeBku[$bkuKontrak->id] = $bku->id;
                $kontrakIds->push($bkuKontrak->id);
            }
        }

        // Pre-load seluruh laporan pada rentang tanggal agar tidak ada query di loop.
        $laporanMap = [];

        LaporanHarian::query()
            ->whereIn('bku_kontrak_id', $kontrakIds->unique()->values())
            ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->get(['bku_kontrak_id', 'tanggal', 'created_at'])
            ->each(function (LaporanHarian $laporan) use (&$laporanMap, $kontrakKeBku) {
                $bkuIdLaporan = $kontrakKeBku[$laporan->bku_kontrak_id] ?? null;

                if (! $bkuIdLaporan) {
                    return;
                }

                $tanggalLaporan = $laporan->tanggal->toDateString();
                $dibuatPadaTanggal = $laporan->created_at?->toDateString();

                $terlambat = $dibuatPadaTanggal !== null
                    && $dibuatPadaTanggal > $tanggalLaporan;

                // Nilai true = hari itu ada laporan terlambat, false = ada laporan tepat waktu.
                $laporanMap[$bkuIdLaporan][$tanggalLaporan] = ($laporanMap[$bkuIdLaporan][$tanggalLaporan] ?? false)
                    || $terlambat;
            });

        $hasil = [];

        foreach ($bkus as $bku) {
            $terlambat = 0;
            $tidakMelapor = 0;
            $tepatWaktu = 0;

            $tanggal = $mulai->copy();

            while ($tanggal->lte($selesai)) {
                if ($tanggal->isWeekday()) {
                    $punyaKontrakAktif = $bku->bkuKontraks->contains(
                        fn ($bkuKontrak) => $bkuKontrak->created_at === null
                            || $bkuKontrak->created_at->copy()->startOfDay()->lt($tanggal)
                    );

                    if ($punyaKontrakAktif) {
                        $terlambatPadaHariItu = $laporanMap[$bku->id][$tanggal->toDateString()] ?? null;

                        if ($terlambatPadaHariItu === null) {
                            $tidakMelapor++;
                        } elseif ($terlambatPadaHariItu) {
                            $terlambat++;
                        } else {
                            $tepatWaktu++;
                        }
                    }
                }

                $tanggal->addDay();
            }

            $totalHariEfektif = $terlambat + $tidakMelapor + $tepatWaktu;

            if ($totalHariEfektif === 0) {
                continue;
            }

            $hasil[] = [
                'bku_id' => $bku->id,
                'nama' => $bku->nama,
                'total_hari_efektif' => $totalHariEfektif,
                'terlambat' => $terlambat,
                'tidak_melapor' => $tidakMelapor,
                'tepat_waktu' => $tepatWaktu,
                'persentase_tepat_waktu' => round(($tepatWaktu / $totalHariEfektif) * 100, 1),
            ];
        }

        // BKU dengan pelanggaran terbanyak tampil lebih dulu.
        usort($hasil, function (array $a, array $b) {
            $selisih = ($b['terlambat'] + $b['tidak_melapor'])
                <=> ($a['terlambat'] + $a['tidak_melapor']);

            return $selisih !== 0 ? $selisih : strcasecmp($a['nama'], $b['nama']);
        });

        return collect($hasil);
    }
}
