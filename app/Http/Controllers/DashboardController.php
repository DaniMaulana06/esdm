<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterDashboardRequest;
use App\Models\Bku;
use App\Models\BkuKontrak;
use App\Models\Kontrak;
use App\Models\LaporanHarian;
use App\Services\LaporanMonitoringService;
use Illuminate\Database\Eloquent\Builder;
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

        /*
        |--------------------------------------------------------------------------
        | Query dasar laporan
        |--------------------------------------------------------------------------
        */

        $laporanQuery = LaporanHarian::query()
            ->whereBetween('tanggal', [$startDate, $endDate]);

        /*
        |--------------------------------------------------------------------------
        | Scope operator ke BKU miliknya
        |--------------------------------------------------------------------------
        */

        if ($user->isOperatorBku()) {
            $laporanQuery->whereHas(
                'bkuKontrak',
                fn(Builder $query) =>
                    $query->where('bku_id', $user->bku_id)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter BKU
        |--------------------------------------------------------------------------
        */

        if ($bkuId) {
            $laporanQuery->whereHas(
                'bkuKontrak',
                fn(Builder $query) =>
                    $query->where('bku_id', $bkuId)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter kontrak
        |--------------------------------------------------------------------------
        */

        if ($kontrakId) {
            $laporanQuery->whereHas(
                'bkuKontrak',
                fn(Builder $query) =>
                    $query->where('kontrak_id', $kontrakId)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | KPI
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Total BKU
        |--------------------------------------------------------------------------
        */

        $bkuQuery = Bku::query();

        if ($user->isOperatorBku()) {
            $bkuQuery->where('id', $user->bku_id);
        }

        if ($bkuId) {
            $bkuQuery->where('id', $bkuId);
        }

        $totalBku = $bkuQuery->count();

        /*
        |--------------------------------------------------------------------------
        | Total kontrak
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Grafik produksi harian
        |--------------------------------------------------------------------------
        */

        $productionChart = (clone $laporanQuery)
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

        /*
        |--------------------------------------------------------------------------
        | Grafik produksi vs lifting
        |--------------------------------------------------------------------------
        */

        $productionLiftingChart = (clone $laporanQuery)
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

        /*
        |--------------------------------------------------------------------------
        | Produksi berdasarkan BKU
        |--------------------------------------------------------------------------
        */

        $productionByBku = (clone $laporanQuery)
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

        /*
        |--------------------------------------------------------------------------
        | Data BKU untuk filter
        |--------------------------------------------------------------------------
        */

        $bkusQuery = Bku::query()
            ->select('id', 'nama')
            ->orderBy('nama');

        if ($user->isOperatorBku()) {
            $bkusQuery->where('id', $user->bku_id);
        }

        $bkus = $bkusQuery->get();

        /*
        |--------------------------------------------------------------------------
        | Data kontrak untuk filter
        |--------------------------------------------------------------------------
        */

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

        $kontraks = $kontraksQuery->get();

        /*
        |--------------------------------------------------------------------------
        | Status pelaporan hari ini
        |--------------------------------------------------------------------------
        |
        | Satu BKU dianggap "sudah melapor" apabila SEMUA kontraknya
        | sudah memiliki laporan untuk hari ini.
        |
        */

        $today = now()->toDateString();

        $reportingBkusQuery = Bku::query()
            ->withCount([
                'bkuKontraks as total_kontrak',
                'bkuKontraks as kontrak_sudah_melapor' => function ($query) use ($today) {
                    $query->whereHas(
                        'laporanHarians',
                        fn($reportQuery) =>
                            $reportQuery->whereDate('tanggal', $today)
                    );
                },
            ])
            ->with([
                'bkuKontraks' => function ($query) use ($today) {
                    $query
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

        /*
        |--------------------------------------------------------------------------
        | Laporan terbaru
        |--------------------------------------------------------------------------
        */

        $latestReports = (clone $laporanQuery)
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

        // $riwayatBkuBelumLapor = $monitoring->getBkuBelumLapor();

        // dd($riwayatBkuBelumLapor);
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_bku' => $totalBku,
                'total_kontrak' => $totalKontrak,
                'average_produksi' => round($averageProduksi, 2),
                'average_lifting' => round($averageLifting, 2),
            ],

            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'bku_id' => $bkuId,
                'kontrak_id' => $kontrakId,
            ],

            'bkus' => $bkus,
            'kontraks' => $kontraks,
            'bkuBelumLapor' => $monitoring->getBkuBelumLapor(),

            'productionChart' => $productionChart,
            'productionLiftingChart' => $productionLiftingChart,
            'productionByBku' => $productionByBku,

            'reportingStatus' => [
                'total_bku' => $totalReportingBku,
                'sudah_melapor' => $sudahMelapor,
                'belum_melapor' => $belumMelapor,
                'percentage' => $reportingPercentage,
            ],

            'missingReports' => $missingReports,
            'latestReports' => $latestReports,
        ]);
    }
}