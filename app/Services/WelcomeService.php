<?php
namespace App\Services;

use App\Models\Bku;
use App\Models\LaporanHarian;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WelcomeService
{
    /**
     * Ambil data BKU beserta total produksi berdasarkan filter bulan & tahun.
     */
    public function getBkuListWithProduction(?string $bulan, ?string $tahun): Collection
    {
        return Bku::query()
            ->leftJoin('bku_kontrak', 'bku.id', '=', 'bku_kontrak.bku_id')
            ->leftJoin('laporan_harian', function ($join) use ($bulan, $tahun) {
                $join->on('bku_kontrak.id', '=', 'laporan_harian.bku_kontrak_id');
                if ($bulan && $bulan !== 'all') {
                    $join->whereMonth('laporan_harian.tanggal', $bulan);
                }
                if ($tahun && $tahun !== 'all') {
                    $join->whereYear('laporan_harian.tanggal', $tahun);
                }
            })
            ->select('bku.id', 'bku.nama', 'bku.kota', 'bku.kategori', 'bku.penetapan')
            ->selectRaw('COALESCE(SUM(laporan_harian.total_produksi), 0) as total_produksi')
            ->groupBy('bku.id', 'bku.nama', 'bku.kota', 'bku.kategori', 'bku.penetapan')
            ->orderBy('bku.nama')
            ->get();
    }

    /**
     * Ambil daftar tahun yang ada laporan produksinya untuk opsi filter.
     */
    // public function getAvailableYears(): array
    // {
    //     $years = LaporanHarian::query()
    //         ->selectRaw('DISTINCT YEAR(tanggal) as tahun')
    //         ->orderByDesc('tahun')
    //         ->pluck('tahun')
    //         ->toArray();

    //     return !empty($years) ? $years : [(int) date('Y')];
    // }

    public function getAvailableYears(): array
    {
        $driver = DB::connection()->getDriverName();

        $yearExpression = $driver === 'sqlite'
            ? "CAST(strftime('%Y', tanggal) AS INTEGER)"
            : 'YEAR(tanggal)';

        $years = LaporanHarian::query()
            ->selectRaw("DISTINCT {$yearExpression} as tahun")
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->toArray();

        return !empty($years) ? $years : [(int) date('Y')];
    }
}