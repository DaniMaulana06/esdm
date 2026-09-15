<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLaporanHarianRequest;
use App\Models\BkuKontrak;
use App\Models\LaporanHarian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LaporanHarianController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = LaporanHarian::with(['bkuKontrak.bku', 'bkuKontrak.kontrak', 'justifikasis']);

        // Jika role operator_bku, filter laporan hanya untuk BKU miliknya
        if ($user->isOperatorBku()) {
            $query->whereHas('bkuKontrak', function ($q) use ($user) {
                $q->where('bku_id', $user->bku_id);
            });
        }

        if ($request->has('bku_id') && $request->bku_id) {
            $query->whereHas('bkuKontrak', function ($q) use ($request) {
                $q->where('bku_id', $request->bku_id);
            });
        }

        if ($request->has('tanggal') && $request->tanggal) {
            $query->where('tanggal', $request->tanggal);
        }

        $laporanHarian = $query->latest('tanggal')->paginate(15)->withQueryString();

        // Opsi BKU Kontrak yang tersedia untuk form input
        $bkuKontrakQuery = BkuKontrak::with(['bku', 'kontrak']);
        if ($user->isOperatorBku()) {
            $bkuKontrakQuery->where('bku_id', $user->bku_id);
        }
        $bkuKontraks = $bkuKontrakQuery->get();

        return Inertia::render('laporan-harian/Index', [
            'laporanHarian' => $laporanHarian,
            'bkuKontraks' => $bkuKontraks,
            'filters' => $request->only(['bku_id', 'tanggal']),
        ]);
    }

    public function store(StoreLaporanHarianRequest $request): RedirectResponse
    {
        LaporanHarian::create($request->validated());

        return redirect()->back()->with('success', 'Laporan produksi & lifting harian berhasil disimpan.');
    }
}
