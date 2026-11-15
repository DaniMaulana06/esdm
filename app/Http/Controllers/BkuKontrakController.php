<?php

namespace App\Http\Controllers;

use App\Filters\BkuKontrakFilter;
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
    public function index(Request $request): Response
    {
        $filter = new BkuKontrakFilter($request);

        $query = BkuKontrak::with([
            'bku:id,nama',
            'kontrak:id,nama',
        ])
            ->withCount('sumurs')
            ->latest();

        $bkuKontraks = $filter->apply($query)
            ->paginate(10)
            ->withQueryString();

        $kontraks = Kontrak::orderBy('nama')->get(['id', 'nama']);
        
        return Inertia::render('BkuKontrak/Index', [
            'bkuKontraks' => $bkuKontraks,
            'kontraks' => $kontraks,
            'filters' => $request->only(['search', 'kontrak_id']),
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

        return redirect()->route('bku-kontrak.index')->with('success', 'Penetapan Kontrak ke BKU berhasil disimpan.');
    }

    public function update(
        UpdateBkuKontrakRequest $request,
        BkuKontrak $bkuKontrak
    ): RedirectResponse {
        $bkuKontrak->update($request->validated());

        return redirect()
            ->route('bku-kontrak.index')
            ->with('success', 'Data BKU Kontrak berhasil diperbarui.');
    }

    public function destroy(BkuKontrak $bkuKontrak): RedirectResponse
    {
        // dd($bkuKontrak);    
        $bkuKontrak->delete();

        return redirect()->route('bku-kontrak.index')->with('success', 'Penetapan BKU Kontrak berhasil dihapus.');
    }
}
