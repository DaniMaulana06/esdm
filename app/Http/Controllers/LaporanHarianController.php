<?php

namespace App\Http\Controllers;

use App\Filters\LaporanHarianFilter;
use App\Http\Requests\FilterLaporanHarianRequest;
use App\Http\Requests\StoreLaporanHarianRequest;
use App\Models\BkuKontrak;
use App\Models\LaporanHarian;
use Cache;
use DB;
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

        // operator hanya melihat kontrak dari BKU miliknya
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

    public function store(StoreLaporanHarianRequest $request): RedirectResponse {
        $user = $request->user();

        $validated = $request->validated();

        $bkuKontraks = BkuKontrak::query()
            ->where('bku_id', $user->bku_id)
            ->pluck('id')
            ->sort()
            ->values();

        $submittedIds = collect($validated['laporan'])
            ->pluck('bku_kontrak_id')
            ->sort()
            ->values();

        //validasi agar seluruh kontrak dilaporakan
        if ( 
            $bkuKontraks->count() !== $submittedIds->count() ||
            $bkuKontraks->diff($submittedIds)->isNotEmpty() ||
            $submittedIds->diff($bkuKontraks)->isNotEmpty()
        ) {
            return back()
                ->withErrors([
                    'laporan' => 'Semua kontrak milik BKU wajib dilaporkan.',
                ])
                ->withInput();
        }

        foreach ($validated['laporan'] as $index => $laporan) { //validasi 1 hari 1 laporan
            $sudahAda = LaporanHarian::query()
                ->where('bku_kontrak_id', $laporan['bku_kontrak_id'])
                ->whereDate('tanggal', $validated['tanggal'])
                ->exists();

            if ($sudahAda) {
                return back()
                    ->withErrors([
                        "laporan.$index.bku_kontrak_id" =>
                            'Laporan untuk kontrak ini pada tanggal tersebut sudah pernah dibuat.',
                    ])
                    ->withInput();
            }
        }

        // simpan laporan dalam 1 transaksi
        DB::transaction(function () use ($validated) {
            foreach ($validated['laporan'] as $laporan) {
                LaporanHarian::create([
                    'bku_kontrak_id' => $laporan['bku_kontrak_id'],
                    'tanggal' => $validated['tanggal'],
                    'total_produksi' => $laporan['total_produksi'],
                    'total_lifting' => $laporan['total_lifting'],
                ]);
            }
        });

        return redirect()->route('laporan-harian.index')->with('success','Laporan produksi & lifting harian berhasil disimpan.');
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
