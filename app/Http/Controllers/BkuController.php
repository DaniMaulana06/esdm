<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBkuRequest;
use App\Http\Requests\UpdateBkuRequest;
use App\Models\Bku;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class BkuController extends Controller
{
    public function index(): Response
    {
        $bkus = Bku::withCount(['kontraks', 'users'])
            ->latest()
            ->get();

        return Inertia::render('Bku/Index', [
            'bkus' => $bkus,
        ]);
    }

    public function store(StoreBkuRequest $request): RedirectResponse
    {
        Bku::create($request->validated());

        return redirect()->back()->with('success', 'Data BKU berhasil ditambahkan.');
    }

    public function update(UpdateBkuRequest $request, Bku $bku): RedirectResponse
    {
        $bku->update($request->validated());

        return redirect()->back()->with('success', 'Data BKU berhasil diperbarui.');
    }

    public function destroy(Bku $bku): RedirectResponse
    {
        $bku->delete();

        return redirect()->back()->with('success', 'Data BKU berhasil dihapus.');
    }
}
