<?php

namespace App\Http\Controllers;

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
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = LaporanHarian::with([
            'bkuKontrak.bku',
            'bkuKontrak.kontrak',
            'justifikasis',
        ]);

        // Operator hanya melihat laporan dari BKU miliknya
        if ($user->isOperatorBku()) {
            $query->whereHas('bkuKontrak', function ($q) use ($user) {
                $q->where('bku_id', $user->bku_id);
            });
        }

        // Filter BKU
        if ($request->filled('bku_id')) {
            $query->whereHas('bkuKontrak', function ($q) use ($request) {
                $q->where('bku_id', $request->bku_id);
            });
        }

        // Filter tanggal
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }

        $laporanHarians = $query
            ->latest('tanggal')
            ->paginate(15)
            ->withQueryString();

        $bkuKontrakQuery = BkuKontrak::with([
            'bku',
            'kontrak',
        ]);

        if ($user->isOperatorBku()) {
            $bkuKontrakQuery->where('bku_id', $user->bku_id);
        }

        $bkuKontraks = $bkuKontrakQuery->get();

        return Inertia::render('LaporanHarian/Index', [
            'laporanHarians' => $laporanHarians,
            'bkuKontraks' => $bkuKontraks,
            'filters' => $request->only(['bku_id', 'tanggal']),
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
