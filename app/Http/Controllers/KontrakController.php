<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKontrakRequest;
use App\Http\Requests\UpdateKontrakRequest;
use App\Models\Kontrak;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class KontrakController extends Controller
{
    public function index(): Response
    {
        $kontraks = Kontrak::withCount('bkus')
            ->latest()
            ->paginate(10);

        return Inertia::render('kontrak/Index', [
            'kontraks' => $kontraks,
        ]);
    }

    public function store(StoreKontrakRequest $request): RedirectResponse
    {
        Kontrak::create($request->validated());

        return redirect()->back()->with('success', 'Data Kontrak berhasil ditambahkan.');
    }

    public function update(UpdateKontrakRequest $request, Kontrak $kontrak): RedirectResponse
    {
        $kontrak->update($request->validated());

        return redirect()->back()->with('success', 'Data Kontrak berhasil diperbarui.');
    }

    public function destroy(Kontrak $kontrak): RedirectResponse
    {
        $kontrak->delete();

        return redirect()->back()->with('success', 'Data Kontrak berhasil dihapus.');
    }
}
