<?php

namespace App\Http\Controllers;

use App\Filters\LaporanHarianFilter;
use App\Http\Requests\FilterLaporanHarianRequest;
use App\Http\Requests\StoreLaporanHarianRequest;
use App\Models\BkuKontrak;
use App\Models\LaporanHarian;
use Cache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LaporanHarianController extends Controller
{
    public function index(
        FilterLaporanHarianRequest $request,
        LaporanHarianFilter $filter
    ): Response {
        $user = $request->user();

        $query = LaporanHarian::query()
            ->with([
                'bkuKontrak.bku',
                'bkuKontrak.kontrak',
                'justifikasis',
            ])
            ->forUser($user);

        // Search, filter, dan sorting
        $filter->apply(
            $query,
            $request->validated()
        );

        $laporanHarians = $query
            ->paginate(15)
            ->withQueryString();

        // BKU + Kontrak untuk kebutuhan halaman
        $bkuKontrakQuery = BkuKontrak::with([
            'bku',
            'kontrak',
        ]);

        // Operator hanya mendapatkan BKU miliknya
        if ($user->isOperatorBku()) {
            $bkuKontrakQuery->where(
                'bku_id',
                $user->bku_id
            );
        }

        $bkuKontraks = $bkuKontrakQuery->get();

        return Inertia::render('LaporanHarian/Index', [
            'laporanHarians' => $laporanHarians,
            'bkuKontraks' => $bkuKontraks,
            'filters' => $request->validated(),
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();

        $query = BkuKontrak::with([
            'bku:id,nama',
            'kontrak:id,nama',
        ]);

        // Operator hanya melihat kontrak dari BKU miliknya
        if ($user->isOperatorBku()) {
            $query->where('bku_id', $user->bku_id);
        }

        $bkuKontraks = $query
            ->orderBy('bku_id')
            ->get();

        return Inertia::render('LaporanHarian/Create', [
            'bkuKontraks' => $bkuKontraks,
        ]);
    }

    public function edit(LaporanHarian $laporanHarian): Response
    {
        $laporanHarian->load([
            'bkuKontrak.bku',
            'bkuKontrak.kontrak',
        ]);

        return Inertia::render('LaporanHarian/Edit', [
            'laporanHarian' => $laporanHarian,
        ]);
    }

    public function store(StoreLaporanHarianRequest $request): RedirectResponse
    {
        LaporanHarian::create($request->validated());

        Cache::forget('laporan_harian.all');

        return redirect()->route('laporan-harian.index')->with('success', 'Laporan produksi & lifting harian berhasil disimpan.');
    }

    public function update(
        Request $request,
        LaporanHarian $laporanHarian
    ): RedirectResponse {
        $validated = $request->validate([
            'total_produksi' => ['required', 'numeric', 'min:0'],
            'total_lifting' => ['required', 'numeric', 'min:0'],
        ]);

        $laporanHarian->update($validated);

        return redirect()
            ->route('laporan-harian.index')
            ->with('success', 'Laporan harian berhasil diperbarui.');
    }

    public function destroy(LaporanHarian $laporanHarian): RedirectResponse
    {
        $laporanHarian->delete();

        // Cache::forget('laporan-harian.all');

        return redirect()->back()->with('success', 'Data Kontrak berhasil dihapus.');
    }
}
