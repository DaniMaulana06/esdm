<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKontrakRequest;
use App\Http\Requests\UpdateKontrakRequest;
use App\Models\Kontrak;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class KontrakController extends Controller
{
    public function index(): Response
    {
        // $kontraks = Kontrak::withCount('bkus')
        //     ->latest()
        //     ->get();

        $kontraks = Cache::remember('kontrak.all', 60 * 60, function(){
            return Kontrak::withCount('bkus')->latest()->get();
        });

        return Inertia::render('Kontrak/Index', [
            'kontraks' => $kontraks,
        ]);
    }

    public function create(): Response
    {
        $kontraks = Kontrak::orderBy('nama')->get(['id', 'nama']);

        return Inertia::render('Kontrak/Create', [
            'kontraks' => $kontraks,
        ]);
    }

    public function edit(Kontrak $kontrak)
    {
        return Inertia::render('Kontrak/Edit', [
            'kontrak' => $kontrak,
        ]);
    }

    public function store(StoreKontrakRequest $request): RedirectResponse
    {
        Kontrak::create($request->validated());

        Cache::forget('kontraks.all');

        return redirect()->route('kontrak.index')->with('success', 'Data Kontrak berhasil ditambahkan.');
    }

    public function update(UpdateKontrakRequest $request, Kontrak $kontrak): RedirectResponse
    {
        $kontrak->update($request->validated());

        Cache::forget('kontraks.all');

        return redirect()->route('kontrak.index')->with('success', 'Data Kontrak berhasil diperbarui.');
    }

    public function destroy(Kontrak $kontrak): RedirectResponse
    {
        $kontrak->delete();

        Cache::forget('kontraks.all');

        return redirect()->back()->with('success', 'Data Kontrak berhasil dihapus.');
    }
}
