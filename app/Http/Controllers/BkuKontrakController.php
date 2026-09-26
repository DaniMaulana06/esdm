<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBkuKontrakRequest;
use App\Http\Requests\UpdateBkuKontrakRequest;
use App\Models\Bku;
use App\Models\BkuKontrak;
use App\Models\Kontrak;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BkuKontrakController extends Controller
{
    public function index(): Response
    {
        $bkuKontraks = Cache::remember('bku_kontrak.all', 60 * 60, function () {
            logger()->info('CACHE MISS - QUERY DATABASE DIJALANKAN');

            return BkuKontrak::with([
                'bku:id,nama',
                'kontrak:id,nama',
            ])
                ->withCount('sumurs')
                ->latest()
                ->get();
        });

        logger()->info('DATA DIAMBIL DARI CACHE ATAU HASIL QUERY');

        return Inertia::render('BkuKontrak/Index', [
            'bkuKontraks' => $bkuKontraks,

            //dibutuhkan jika ingin menampilkan dropdown list untuk memilih BKU dan Kontrak saat membuat atau mengedit BkuKontrak
            // 'bkus' => Bku::select('id', 'nama')->get(),
            // 'kontraks' => Kontrak::select('id', 'nama')->get(),
        ]);
    }

    public function create(): Response
    {
        $bkus = Bku::orderBy('nama')->get([
            'id',
            'nama',
        ]);

        $kontraks = Kontrak::orderBy('nama')->get([
            'id',
            'nama',
        ]);

        Cache::forget('bku_kontrak.all');

        return Inertia::render('BkuKontrak/Create', [
            'bkus' => $bkus,
            'kontraks' => $kontraks,
        ]);
    }

    public function edit(BkuKontrak $bkuKontrak): Response
    {
        $bkuKontrak->load([
            'bku:id,nama',
            'kontrak:id,nama',
        ]);

        return Inertia::render('BkuKontrak/Edit', [
            'bkuKontrak' => $bkuKontrak,
            'bkus' => Bku::select('id', 'nama')->get(),
            'kontraks' => Kontrak::select('id', 'nama')->get(),
        ]);
    }

    public function store(StoreBkuKontrakRequest $request): RedirectResponse
    {
        BkuKontrak::create($request->validated());

        Cache::forget('bku_kontrak.all');
        
        return redirect()->route('bku-kontrak.index')->with('success', 'Penetapan Kontrak ke BKU berhasil disimpan.');
    }

    public function update(
        UpdateBkuKontrakRequest $request,
        BkuKontrak $bkuKontrak
    ): RedirectResponse {
        $bkuKontrak->update($request->validated());

        Cache::forget('bku_kontrak.all');

        return redirect()
            ->route('bku-kontrak.index')
            ->with('success', 'Data BKU Kontrak berhasil diperbarui.');
    }
    
    public function destroy(BkuKontrak $bkuKontrak): RedirectResponse
    {
        // dd($bkuKontrak);    
        $bkuKontrak->delete();

        Cache::forget('bku_kontrak.all');

        return redirect()->route('bku-kontrak.index')->with('success', 'Penetapan BKU Kontrak berhasil dihapus.');
    }
}
