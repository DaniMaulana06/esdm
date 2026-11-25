<?php

namespace App\Services;

use app\Services\LaporanMonitoringService;
use App\Models\Bku;
use App\Models\BkuKontrak;
use App\Models\LaporanHarian;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;

class DashboardPdfService
{
    public function generate(
        User $user,
        string $startDate,
        string $endDate,
        $bkuId = null,
        $kontrakId = null,
        ?LaporanMonitoringService $monitoring = null
    ) {
        $rekapBku = $this->buildProductionRecap(
            $user,
            $startDate,
            $endDate,
            $bkuId,
            $kontrakId
        );

        $totalProduksi = $rekapBku->sum('total_produksi');
        $totalLifting = $rekapBku->sum('total_lifting');

        $dates = collect(
            CarbonPeriod::create(
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->startOfDay()
            )
        );

        $rekapPages = $this->paginateRecap(
            $rekapBku,
            $dates,
            28 // perkiraan jumlah baris per halaman
        );

        $data = compact(
            'startDate',
            'endDate',
            'rekapPages',
            'totalProduksi',
            'totalLifting'
        );

        return Pdf::loadView('pdf.dashboard', $data)
            ->setPaper('a4', 'landscape');
    }

    private function paginateRecap($rekapBku, $dates, int $rowsPerPage = 18): array
    {
        $pages = [];
        $currentPage = [];
        $remainingRows = $rowsPerPage;
        $bkuIndex = 0;

        foreach ($rekapBku->values() as $bkuIndex => $bku) {
            $remainingDates = $dates->values();

            while ($remainingDates->isNotEmpty()) {
                $take = min($remainingRows, $remainingDates->count());

                $currentPage[] = [
                    'bku' => $bku,
                    'nomor' => $bkuIndex + 1,
                    'dates' => $remainingDates->take($take)->values(),
                ];

                $remainingDates = $remainingDates->slice($take)->values();
                $remainingRows -= $take;

                if ($remainingRows === 0) {
                    $pages[] = $currentPage;

                    $currentPage = [];
                    $remainingRows = $rowsPerPage;
                }
            }
        }

        if (!empty($currentPage)) {
            $pages[] = $currentPage;
        }

        return $pages;
    }

    private function buildProductionRecap(
        User $user,
        string $startDate,
        string $endDate,
        $bkuId = null,
        $kontrakId = null
    ) {
        // ambil data bku beserta kontrak dan jumlah sumur
        $bkuQuery = Bku::query()
            ->with([
                'bkuKontraks' => function ($query) use ($kontrakId) {
                    if ($kontrakId) {
                        $query->where('kontrak_id', $kontrakId);
                    }

                    $query->with([
                        'kontrak',
                        'sumurs',
                    ]);
                },
            ])
            ->orderBy('nama');

        if ($user->isOperatorBku()) {
            $bkuQuery->where('id', $user->bku_id);
        }

        if ($bkuId) {
            $bkuQuery->where('id', $bkuId);
        }

        $bkus = $bkuQuery->get();

        //ambil rekap laporan berdasarkan bku, kontrak dan tanggal
        $laporanQuery = LaporanHarian::query()
            ->whereBetween('tanggal', [$startDate, $endDate]);

        $this->applyLaporanScope(
            $laporanQuery,
            $user,
            $bkuId,
            $kontrakId
        );

        $laporan = $laporanQuery
            ->join(
                'bku_kontrak',
                'laporan_harian.bku_kontrak_id',
                '=',
                'bku_kontrak.id'
            )
            ->join(
                'kontrak',
                'bku_kontrak.kontrak_id',
                '=',
                'kontrak.id'
            )
            ->selectRaw('
                bku_kontrak.bku_id as bku_id,
                bku_kontrak.kontrak_id as kontrak_id,
                kontrak.nama as nama_kontrak,
                DATE(laporan_harian.tanggal) as tanggal,
                SUM(laporan_harian.total_produksi) as total_produksi,
                SUM(laporan_harian.total_lifting) as total_lifting
            ')
            ->groupBy(
                'bku_kontrak.bku_id',
                'bku_kontrak.kontrak_id',
                'kontrak.nama',
                'tanggal'
            )
            ->get();

        //susun data rekapitulasi setiap bku
        return $bkus->map(function ($bku) use ($laporan) {

            $sumurPertamina = 0;
            $sumurMedco = 0;

            foreach ($bku->bkuKontraks as $bkuKontrak) {
                $namaKontrak = strtolower(
                    $bkuKontrak->kontrak?->nama ?? ''
                );

                if (str_contains($namaKontrak, 'pertamina')) {
                    $sumurPertamina += $bkuKontrak->sumurs->count();
                }

                if (str_contains($namaKontrak, 'medco')) {
                    $sumurMedco += $bkuKontrak->sumurs->count();
                }
            }

            // ambil laporan milik bku
            $laporanBku = $laporan->where('bku_id', $bku->id);

            //kelompokan laporan berdasarkan tanggal
            $harian = [];

            foreach ($laporanBku as $item) {
                $tanggal = Carbon::parse($item->tanggal)->format('Y-m-d');

                $namaKontrak = strtolower($item->nama_kontrak);

                if (!isset($harian[$tanggal])) {
                    $harian[$tanggal] = [
                        'produksi_pertamina' => 0,
                        'produksi_medco' => 0,
                        'lifting_pertamina' => 0,
                        'lifting_medco' => 0,
                    ];
                }

                if (str_contains($namaKontrak, 'pertamina')) {
                    $harian[$tanggal]['produksi_pertamina'] +=
                        (float) $item->total_produksi;

                    $harian[$tanggal]['lifting_pertamina'] +=
                        (float) $item->total_lifting;
                }

                if (str_contains($namaKontrak, 'medco')) {
                    $harian[$tanggal]['produksi_medco'] +=
                        (float) $item->total_produksi;

                    $harian[$tanggal]['lifting_medco'] +=
                        (float) $item->total_lifting;
                }
            }

            //hitung total produksi dan lifting

            $totalProduksi = $laporanBku->sum('total_produksi');
            $totalLifting = $laporanBku->sum('total_lifting');

            return [
                'id' => $bku->id,
                'nama' => $bku->nama,
                'penetapan' => $bku->penetapan,

                'sumur_pertamina' => $sumurPertamina,
                'sumur_medco' => $sumurMedco,

                'total_produksi' => $totalProduksi,
                'total_lifting' => $totalLifting,

                'harian' => $harian,
            ];
        });
    }

    private function applyLaporanScope(
        Builder $query,
        User $user,
        $bkuId,
        $kontrakId
    ): void {
        if ($user->isOperatorBku()) {
            $query->whereHas(
                'bkuKontrak',
                fn(Builder $q) => $q->where('bku_id', $user->bku_id)
            );
        }

        if ($bkuId) {
            $query->whereHas(
                'bkuKontrak',
                fn(Builder $q) => $q->where('bku_id', $bkuId)
            );
        }

        if ($kontrakId) {
            $query->whereHas(
                'bkuKontrak',
                fn(Builder $q) => $q->where('kontrak_id', $kontrakId)
            );
        }
    }
}
