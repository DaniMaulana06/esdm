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
        $sumurs = Cache::remember('sumur.all', 60 * 60, function () {
            return Sumur::with([
                'bkuKontrak.bku:id,nama',
                'bkuKontrak.kontrak:id,nama'
            ])
                ->latest()
                ->get();
        });

        // $bkuKontraks = BkuKontrak::with(['bku', 'kontrak'])->get();

        return Inertia::render('Sumur/Index', [
            'sumurs' => $sumurs,
            // 'bkuKontraks' => $bkuKontraks,
        ]);
    }

    public function create(): Response
    {
        $bkuKontraks = BkuKontrak::with([
            'bku:id,nama',
            'kontrak:id,nama'
        ])->orderBy('id')->get();

        return Inertia::render('Sumur/Create', [
            'bkuKontraks' => $bkuKontraks,
        ]);
    }

    public function store(StoreSumurRequest $request): RedirectResponse
    {
        // dd($request->validated());
        Sumur::create($request->validated());

        Cache::forget('sumur.all');

        return redirect()->route('sumur.index')->with('success', 'Data Sumur berhasil ditambahkan.');
    }

    public function edit(Sumur $sumur): Response
    {
        $bkuKontraks = BkuKontrak::with([
            'bku:id,nama',
            'kontrak:id,nama',
        ])
            ->orderBy('id')
            ->get();

        return Inertia::render('Sumur/Edit', [
            'sumur' => $sumur,
            'bkuKontraks' => $bkuKontraks,
        ]);
    }

    public function update(
        UpdateSumurRequest $request,
        Sumur $sumur
    ): RedirectResponse {
        $sumur->update($request->validated());

        Cache::forget('sumur.all');

        return redirect()->route('sumur.index')->with('success', 'Data Sumur berhasil diperbarui.');
    }

    public function destroy(Sumur $sumur): RedirectResponse
    {
        $sumur->delete();

        Cache::forget('sumur.all');

        return redirect()->back()->with('success', 'Data Sumur berhasil dihapus.');
    }
}
