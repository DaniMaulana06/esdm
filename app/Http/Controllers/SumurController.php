<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSumurRequest;
use App\Http\Requests\UpdateSumurRequest;
use App\Models\BkuKontrak;
use App\Models\Sumur;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class SumurController extends Controller
{
    public function index(): Response
    {
        $sumurs = Sumur::with(['bkuKontrak.bku', 'bkuKontrak.kontrak'])
            ->latest()
            ->get();

        $bkuKontraks = BkuKontrak::with(['bku', 'kontrak'])->get();

        return Inertia::render('Sumur/Index', [
            'sumurs' => $sumurs,
            'bkuKontraks' => $bkuKontraks,
        ]);
    }

    public function create()
    {
        $sumurs = Sumur::orderBy('nama_sumur')->get(['id', 'nama_sumur']);

        Cache::forget('bku.all');

        return Inertia::render('Sumur/Create', [
            'sumurs' => $sumurs,
        ]);
    }

    public function store(StoreSumurRequest $request): RedirectResponse
    {
        Sumur::create($request->validated());

        return redirect()->back()->with('success', 'Data Sumur berhasil ditambahkan.');
    }

    public function update(UpdateSumurRequest $request, Sumur $sumur): RedirectResponse
    {
        $sumur->update($request->validated());

        return redirect()->back()->with('success', 'Data Sumur berhasil diperbarui.');
    }

    public function destroy(Sumur $sumur): RedirectResponse
    {
        $sumur->delete();

        return redirect()->back()->with('success', 'Data Sumur berhasil dihapus.');
    }
}
