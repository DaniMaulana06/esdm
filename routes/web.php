<?php

use App\Http\Controllers\Auth\EmailOtpVerificationController;
use App\Http\Controllers\BkuController;
use App\Http\Controllers\BkuKontrakController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JustifikasiController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\LaporanHarianController;
use App\Http\Controllers\SumurController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Bku;

Route::get('/', function () {
    $bkuList = Bku::query()
        ->leftJoin('bku_kontrak', 'bku.id', '=', 'bku_kontrak.bku_id')
        ->leftJoin('laporan_harian', 'bku_kontrak.id', '=', 'laporan_harian.bku_kontrak_id')
        ->select('bku.id', 'bku.nama', 'bku.kota', 'bku.penetapan')
        ->selectRaw('COALESCE(SUM(laporan_harian.total_produksi), 0) as total_produksi')
        ->groupBy('bku.id', 'bku.nama', 'bku.kota', 'bku.penetapan')
        ->orderBy('bku.nama')
        ->get();

    return Inertia::render('Welcome', [
        'bkuList' => $bkuList,
    ]);
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get(
        '/verifikasi-email',
        [EmailOtpVerificationController::class, 'show']
    )->name('email-otp.show');

    Route::post(
        '/verifikasi-email/kirim',
        [EmailOtpVerificationController::class, 'send']
    )->middleware('throttle:3,1')
        ->name('email-otp.send');

    Route::post(
        '/verifikasi-email/konfirmasi',
        [EmailOtpVerificationController::class, 'verify']
    )->middleware('throttle:10,1')
        ->name('email-otp.verify');
});

Route::middleware(['auth', 'email.verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    Route::middleware('admin-or-staf')->group(function () {
        Route::get('bku', [BkuController::class, 'index'])->name('bku.index');
        Route::get('kontrak', [KontrakController::class, 'index'])->name('kontrak.index');
        Route::get('bku-kontrak', [BkuKontrakController::class, 'index'])->name('bku-kontrak.index');
        Route::get('sumur', [SumurController::class, 'index'])->name('sumur.index');
        Route::get('laporan-harian', [LaporanHarianController::class, 'index'])->name('laporan-harian.index');
    });

    Route::get('sumur', [SumurController::class, 'index'])->name('sumur.index');

    Route::middleware('staf-or-operator-bku')->group(function () {
        Route::get('sumur/create', [SumurController::class, 'create'])->name('sumur.create');
        Route::post('sumur', [SumurController::class, 'store'])->name('sumur.store');
    });

    Route::middleware('staf_esdm')->group(function () {
        Route::resource('bku', BkuController::class)->except(['show', 'index']);
        Route::resource('kontrak', KontrakController::class)->except(['show', 'index']);
        Route::resource('bku-kontrak', BkuKontrakController::class)->except(['show', 'index']);

        Route::get('sumur/{sumur}/edit', [SumurController::class, 'edit'])->name('sumur.edit');
        Route::put('sumur/{sumur}', [SumurController::class, 'update'])->name('sumur.update');
        Route::delete('sumur/{sumur}', [SumurController::class, 'destroy'])->name('sumur.destroy');

        Route::get('laporan-harian/{laporan_harian}/edit', [LaporanHarianController::class, 'edit'])->name('laporan-harian.edit');
        Route::put('laporan-harian/{laporan_harian}', [LaporanHarianController::class, 'update'])->name('laporan-harian.update');

        Route::put('justifikasi/{justifikasi}/process', [JustifikasiController::class, 'process'])->name('justifikasi.process');
        
    });
    
    Route::get('justifikasi', [JustifikasiController::class, 'index'])->name('justifikasi.index');
    Route::get('laporan-harian', [LaporanHarianController::class, 'index'])->name('laporan-harian.index');

    Route::middleware('operator-bku')->group(function () {
        Route::get('laporan-harian/create', [LaporanHarianController::class, 'create'])->name('laporan-harian.create');
        Route::get('laporan-harian/create-backfill', [LaporanHarianController::class, 'createBackfill'])->name('laporan-harian.create-backfill');
        Route::post('laporan-harian', [LaporanHarianController::class, 'store'])->name('laporan-harian.store');

        Route::post(
            'justifikasi',
            [JustifikasiController::class, 'store']
        )->name('justifikasi.store');
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
