<?php

namespace App\Services;

use app\Services\LaporanMonitoringService;
use App\Models\Bku;
use App\Models\BkuKontrak;
use App\Models\LaporanHarian;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $laporanQuery = LaporanHarian::query()
            ->whereBetween('tanggal', [$startDate, $endDate]);

        $this->applyLaporanScope(
            $laporanQuery,
            $user,
            $bkuId,
            $kontrakId
        );

        $totalProduksi = (clone $laporanQuery)->sum('total_produksi');
        $totalLifting = (clone $laporanQuery)->sum('total_lifting');

        $jumlahHari = (clone $laporanQuery)
            ->select('tanggal')
            ->distinct()
            ->count();

        $bkuQuery = Bku::query();

        if ($bkuId) {
            $bkuQuery->where('id', $bkuId);
        }

        $bkuKontrakQuery = BkuKontrak::query();

        if ($bkuId) {
            $bkuKontrakQuery->where('bku_id', $bkuId);
        }

        if ($kontrakId) {
            $bkuKontrakQuery->where('kontrak_id', $kontrakId);
        }

        $stats = [
            'total_bku' => $bkuQuery->count(),
            'total_kontrak' => $bkuKontrakQuery
                ->distinct('kontrak_id')
                ->count('kontrak_id'),
            'average_produksi' => $jumlahHari > 0
                ? round($totalProduksi / $jumlahHari, 2)
                : 0,
            'average_lifting' => $jumlahHari > 0
                ? round($totalLifting / $jumlahHari, 2)
                : 0,
        ];

        $productionLiftingChart = (clone $laporanQuery)
            ->selectRaw('DATE(tanggal) as tanggal')
            ->selectRaw('SUM(total_produksi) as total_produksi')
            ->selectRaw('SUM(total_lifting) as total_lifting')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $productionByBku = (clone $laporanQuery)
            ->join(
                'bku_kontrak',
                'laporan_harian.bku_kontrak_id',
                '=',
                'bku_kontrak.id'
            )
            ->join('bku', 'bku_kontrak.bku_id', '=', 'bku.id')
            ->select('bku.id', 'bku.nama')
            ->selectRaw('SUM(laporan_harian.total_produksi) as total_produksi')
            ->groupBy('bku.id', 'bku.nama')
            ->orderByDesc('total_produksi')
            ->get();

        $latestReports = (clone $laporanQuery)
            ->with([
                'bkuKontrak.bku',
                'bkuKontrak.kontrak',
            ])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(10)
            ->get();



        $reporting = $this->buildReportingStatus($user);
        $reportingStatus = $reporting['status'];
        $missingReports = $reporting['missing'];

        $data = compact(
            'startDate',
            'endDate',
            'stats',
            'productionLiftingChart',
            'productionByBku',
            'latestReports',
            'reportingStatus',
            'missingReports'
        );
        return Pdf::loadView('pdf.dashboard', $data)
            ->setPaper('a4', 'landscape');
    }


    private function buildReportingStatus(User $user): array
    {
        $today = now()->toDateString();

        $reportingBkusQuery = Bku::query()
            ->withCount([
                'bkuKontraks as total_kontrak' => function ($query) use ($today) {
                    $query->whereDate('created_at', '<', $today);
                },
                'bkuKontraks as kontrak_sudah_melapor' => function ($query) use ($today) {
                    $query->whereDate('created_at', '<', $today)
                        ->whereHas(
                            'laporanHarians',
                            fn($reportQuery) => $reportQuery->whereDate('tanggal', $today)
                        );
                },
            ])
            ->with([
                'bkuKontraks' => function ($query) use ($today) {
                    $query
                        ->whereDate('created_at', '<', $today)
                        ->with('kontrak')
                        ->whereDoesntHave(
                            'laporanHarians',
                            fn($reportQuery) => $reportQuery->whereDate('tanggal', $today)
                        );
                },
            ])
            ->orderBy('nama');

        if ($user->isOperatorBku()) {
            $reportingBkusQuery->where('id', $user->bku_id);
        }

        $reportingBkus = $reportingBkusQuery->get();

        $totalReportingBku = $reportingBkus->count();

        $sudahMelapor = $reportingBkus
            ->filter(
                fn($bku) =>
                    $bku->total_kontrak > 0 &&
                    $bku->kontrak_sudah_melapor >= $bku->total_kontrak
            )
            ->count();

        $belumMelapor = $totalReportingBku - $sudahMelapor;

        $reportingPercentage = $totalReportingBku > 0
            ? round(($sudahMelapor / $totalReportingBku) * 100, 1)
            : 0;

        $missingReports = $reportingBkus
            ->filter(
                fn($bku) => $bku->total_kontrak > $bku->kontrak_sudah_melapor
            )
            ->map(fn($bku) => [
                'id' => $bku->id,
                'nama' => $bku->nama,
                'missing_count' => $bku->total_kontrak - $bku->kontrak_sudah_melapor,
                'contracts' => $bku->bkuKontraks
                    ->map(fn($bkuKontrak) => [
                        'id' => $bkuKontrak->id,
                        'nama' => $bkuKontrak->kontrak->nama,
                    ])
                    ->values(),
            ])
            ->values();

        return [
            'status' => [
                'total_bku' => $totalReportingBku,
                'sudah_melapor' => $sudahMelapor,
                'belum_melapor' => $belumMelapor,
                'percentage' => $reportingPercentage,
            ],
            'missing' => $missingReports,
        ];
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
