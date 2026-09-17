<?php

use App\Http\Controllers\BkuController;
use App\Http\Controllers\BkuKontrakController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\LaporanHarianController;
use App\Http\Controllers\SumurController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::middleware('staf_esdm')->group(function () {
        Route::resource('bku', BkuController::class)->except(['show']);
        Route::resource('sumur', SumurController::class)->except(['show']);
        Route::resource('kontrak', KontrakController::class)->except(['show']);
        Route::resource('bku-kontrak', BkuKontrakController::class)->except(['show']);
        
    });
    Route::get('laporan-harian', [LaporanHarianController::class, 'index'])->name('laporan-harian.index');

    Route::middleware('operator-bku')->group(function () {
        // Route::get('laporan-harian', [LaporanHarianController::class, 'index'])->name('laporan-harian.index');
        Route::get('laporan-harian/create', [LaporanHarianController::class, 'create'])->name('laporan-harian.create');
        Route::post('laporan-harian', [LaporanHarianController::class, 'store'])->name('laporan-harian.store');
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
