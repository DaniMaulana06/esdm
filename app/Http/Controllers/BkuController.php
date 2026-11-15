<?php

namespace App\Http\Controllers;

use App\Enums\KategoriBku;
use App\Filters\BkuFilter;
use App\Http\Requests\StoreBkuRequest;
use App\Http\Requests\UpdateBkuRequest;
use App\Models\Bku;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BkuController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = new BkuFilter($request);

        $bkus = $filter->apply(Bku::query())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Bku/Index', [
            'bkus'      => $bkus,
            'kategoris' => KategoriBku::values(),
            'filters'   => $request->only(['search', 'kategori']),
        ]);
    }

    public function create(): Response
    {
        $bkus = Bku::orderBy('nama')->get(['id', 'nama']);

        Cache::forget('bku.all');

        return Inertia::render('Bku/Create', [
            'bkus' => $bkus,
            'kategoris' => KategoriBku::values(),
        ]);
    }

    public function store(StoreBkuRequest $request): RedirectResponse
    {
        // dd($request->validated());
        Bku::create($request->validated());

        Cache::forget('bku.all');

        return redirect()->route('bku.index')->with('success', 'Data BKU berhasil ditambahkan.');
    }

    public function edit(Bku $bku)
    {
        return Inertia::render('Bku/Edit', [
            'bku' => $bku,
            'kategoris' => KategoriBku::values(),
        ]);
    }

    public function update(UpdateBkuRequest $request, Bku $bku): RedirectResponse
    {
        $bku->update($request->validated());

        Cache::forget('bku.all');

        return redirect()->route('bku.index')->with('success', 'Data BKU berhasil diperbarui.');
    }

    public function destroy(Bku $bku): RedirectResponse
    {
        $bku->delete();

        Cache::forget('bku.all');

        return redirect()->route('bku.index')->with('success', 'Data BKU berhasil dihapus.');
    }
}
