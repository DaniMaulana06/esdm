<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterDashboardRequest;
use App\Models\Bku;
use App\Models\BkuKontrak;
use App\Models\Kontrak;
use App\Models\LaporanHarian;
use App\Models\User;
use App\Services\LaporanMonitoringService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(FilterDashboardRequest $request, LaporanMonitoringService $monitoring): Response
    {
        $user = $request->user();

        $startDate = $request->validated('start_date')
            ?? now()->startOfMonth()->toDateString();

        $endDate = $request->validated('end_date')
            ?? now()->toDateString();

        $bkuId = $request->validated('bku_id');
        $kontrakId = $request->validated('kontrak_id');

        $laporanQuery = $this->buildLaporanQuery($user, $startDate, $endDate, $bkuId, $kontrakId);

        $reporting = $this->buildReportingStatus($user);

        return Inertia::render('Dashboard', [
            'stats' => $this->buildStats($laporanQuery, $user, $bkuId, $kontrakId),

            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'bku_id' => $bkuId,
                'kontrak_id' => $kontrakId,
            ],

            'bkus' => $this->getBkus($user),
            'kontraks' => $this->getKontraks($user, $bkuId),
            'bkuBelumLapor' => $monitoring->getBkuBelumLapor(),

            'productionChart' => $this->getProductionChart($laporanQuery),
            'productionLiftingChart' => $this->getProductionLiftingChart($laporanQuery),
            'productionMonthlyChart' => $this->getProductionMonthlyChart($user, $bkuId, $kontrakId),
            'productionByBku' => $this->getProductionByBku($laporanQuery),

            'reportingStatus' => $reporting['status'],
            'missingReports' => $reporting['missing'],

            'latestReports' => $this->getLatestReports($laporanQuery),
            'missingDatesForOperator' => $this->getMissingDatesForOperator($user, $monitoring),
        ]);
    }

    /**
     * Scope laporan: operator BKU hanya BKU miliknya, plus filter BKU & kontrak.
     */
    private function applyLaporanScope(Builder $query, User $user, $bkuId, $kontrakId): void
    {
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

    /**
     * Query dasar laporan (rentang tanggal + scope user + filter).
     */
    private function buildLaporanQuery(User $user, string $startDate, string $endDate, $bkuId, $kontrakId): Builder
    {
        $query = LaporanHarian::query()
            ->whereBetween('tanggal', [$startDate, $endDate]);

        $this->applyLaporanScope($query, $user, $bkuId, $kontrakId);

        return $query;
    }

    /**
     * KPI: total BKU, total kontrak, rata-rata produksi & lifting.
     */
    private function buildStats(Builder $laporanQuery, User $user, $bkuId, $kontrakId): array
    {
        $totalProduksi = (clone $laporanQuery)->sum('total_produksi');
        $totalLifting = (clone $laporanQuery)->sum('total_lifting');

        $jumlahHari = (clone $laporanQuery)
            ->select('tanggal')
            ->distinct()
            ->count();

        $averageProduksi = $jumlahHari > 0
            ? $totalProduksi / $jumlahHari
            : 0;

        $averageLifting = $jumlahHari > 0
            ? $totalLifting / $jumlahHari
            : 0;

        // Total BKU
        $bkuQuery = Bku::query();

        if ($user->isOperatorBku()) {
            $bkuQuery->where('id', $user->bku_id);
        }

        if ($bkuId) {
            $bkuQuery->where('id', $bkuId);
        }

        $totalBku = $bkuQuery->count();

        // Total kontrak
        $bkuKontrakQuery = BkuKontrak::query();

        if ($user->isOperatorBku()) {
            $bkuKontrakQuery->where('bku_id', $user->bku_id);
        }

        if ($bkuId) {
            $bkuKontrakQuery->where('bku_id', $bkuId);
        }

        if ($kontrakId) {
            $bkuKontrakQuery->where('kontrak_id', $kontrakId);
        }

        $totalKontrak = $bkuKontrakQuery
            ->distinct('kontrak_id')
            ->count('kontrak_id');

        return [
            'total_bku' => $totalBku,
            'total_kontrak' => $totalKontrak,
            'average_produksi' => round($averageProduksi, 2),
            'average_lifting' => round($averageLifting, 2),
        ];
    }

    /**
     * Grafik produksi harian.
     */
    private function getProductionChart(Builder $laporanQuery): Collection
    {
        return (clone $laporanQuery)
            ->selectRaw('DATE(tanggal) as tanggal')
            ->selectRaw('SUM(total_produksi) as total_produksi')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->map(fn($item) => [
                'tanggal' => $item->tanggal,
                'total_produksi' => (float) $item->total_produksi,
            ])
            ->values();
    }

    /**
     * Data produksi vs lifting per tanggal.
     */
    private function getProductionLiftingChart(Builder $laporanQuery): Collection
    {
        return (clone $laporanQuery)
            ->selectRaw('DATE(tanggal) as tanggal')
            ->selectRaw('SUM(total_produksi) as total_produksi')
            ->selectRaw('SUM(total_lifting) as total_lifting')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->map(fn($item) => [
                'tanggal' => $item->tanggal,
                'total_produksi' => (float) $item->total_produksi,
                'total_lifting' => (float) $item->total_lifting,
            ])
            ->values();
    }

    /**
     * Grafik produksi vs lifting per bulan (12 bulan, total semua BKU).
     */
    private function getProductionMonthlyChart(User $user, $bkuId, $kontrakId): Collection
    {
        $chartStart = now()->startOfMonth()->subMonthNoOverflow(11);
        $chartEnd = now()->endOfMonth();

        $monthlyQuery = LaporanHarian::query()
            ->whereBetween('tanggal', [$chartStart->toDateString(), $chartEnd->toDateString()]);

        $this->applyLaporanScope($monthlyQuery, $user, $bkuId, $kontrakId);

        $monthlyRows = $monthlyQuery
            ->selectRaw('EXTRACT(YEAR FROM tanggal) as tahun')
            ->selectRaw('EXTRACT(MONTH FROM tanggal) as bulan')
            ->selectRaw('SUM(total_produksi) as total_produksi')
            ->selectRaw('SUM(total_lifting) as total_lifting')
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(fn($row) => sprintf('%04d-%02d', $row->tahun, $row->bulan));

        // Isi semua bulan (bulan tanpa data = 0) agar grafik tidak bolong
        return collect(range(0, 11))
            ->map(function ($i) use ($chartStart, $monthlyRows) {
                $month = $chartStart->copy()->addMonths($i);
                $key = $month->format('Y-m');
                $row = $monthlyRows->get($key);

                return [
                    'bulan' => $key,
                    'total_produksi' => (float) ($row->total_produksi ?? 0),
                    'total_lifting' => (float) ($row->total_lifting ?? 0),
                ];
            })
            ->values();
    }

    /**
     * Produksi berdasarkan BKU.
     */
    private function getProductionByBku(Builder $laporanQuery): Collection
    {
        return (clone $laporanQuery)
            ->join(
                'bku_kontrak',
                'laporan_harian.bku_kontrak_id',
                '=',
                'bku_kontrak.id'
            )
            ->join(
                'bku',
                'bku_kontrak.bku_id',
                '=',
                'bku.id'
            )
            ->select(
                'bku.id',
                'bku.nama'
            )
            ->selectRaw(
                'SUM(laporan_harian.total_produksi) as total_produksi'
            )
            ->groupBy(
                'bku.id',
                'bku.nama'
            )
            ->orderByDesc('total_produksi')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'nama' => $item->nama,
                'total_produksi' => (float) $item->total_produksi,
            ])
            ->values();
    }

    /**
     * Data BKU untuk filter.
     */
    private function getBkus(User $user): Collection
    {
        $bkusQuery = Bku::query()
            ->select('id', 'nama')
            ->orderBy('nama');

        if ($user->isOperatorBku()) {
            $bkusQuery->where('id', $user->bku_id);
        }

        return $bkusQuery->get();
    }

    /**
     * Data kontrak untuk filter.
     */
    private function getKontraks(User $user, $bkuId): Collection
    {
        $kontraksQuery = Kontrak::query()
            ->select('id', 'nama')
            ->orderBy('nama');

        if ($user->isOperatorBku()) {
            $kontraksQuery->whereHas(
                'bkuKontraks',
                fn(Builder $query) =>
                    $query->where('bku_id', $user->bku_id)
            );
        }

        if ($bkuId) {
            $kontraksQuery->whereHas(
                'bkuKontraks',
                fn(Builder $query) =>
                    $query->where('bku_id', $bkuId)
            );
        }

        return $kontraksQuery->get();
    }

    /**
     * Status pelaporan hari ini.
     *
     * Satu BKU dianggap "sudah melapor" apabila SEMUA kontraknya
     * sudah memiliki laporan untuk hari ini.
     *
     * @return array{status: array, missing: Collection}
     */
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
                            fn($reportQuery) =>
                                $reportQuery->whereDate('tanggal', $today)
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
                            fn($reportQuery) =>
                                $reportQuery->whereDate('tanggal', $today)
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
                fn($bku) =>
                    $bku->total_kontrak > $bku->kontrak_sudah_melapor
            )
            ->map(fn($bku) => [
                'id' => $bku->id,
                'nama' => $bku->nama,
                'missing_count' =>
                    $bku->total_kontrak -
                    $bku->kontrak_sudah_melapor,
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

    /**
     * 10 laporan terbaru.
     */
    private function getLatestReports(Builder $laporanQuery): Collection
    {
        return (clone $laporanQuery)
            ->with([
                'bkuKontrak.bku',
                'bkuKontrak.kontrak',
            ])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn($laporan) => [
                'id' => $laporan->id,
                'tanggal' => $laporan->tanggal->toDateString(),
                'bku' => $laporan->bkuKontrak->bku->nama,
                'kontrak' => $laporan->bkuKontrak->kontrak->nama,
                'total_produksi' => (float) $laporan->total_produksi,
                'total_lifting' => (float) $laporan->total_lifting,
            ]);
    }

    /**
     * Tanggal terlewat khusus untuk operator BKU.
     */
    private function getMissingDatesForOperator(User $user, LaporanMonitoringService $monitoring): array|Collection
    {
        if ($user->isOperatorBku() && $user->bku_id) {
            return $monitoring->getMissingDatesForBku($user->bku_id);
        }

        return [];
    }
}