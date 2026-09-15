<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBkuKontrakRequest;
use App\Models\Bku;
use App\Models\BkuKontrak;
use App\Models\Kontrak;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BkuKontrakController extends Controller
{
    public function index(): Response
    {
        $bkuKontraks = BkuKontrak::with(['bku', 'kontrak'])
            ->withCount('sumurs')
            ->latest()
            ->paginate(10);

        return Inertia::render('bku-kontrak/Index', [
            'bkuKontraks' => $bkuKontraks,
            'bkus' => Bku::select('id', 'nama')->get(),
            'kontraks' => Kontrak::select('id', 'nama')->get(),
        ]);
    }

    public function store(StoreBkuKontrakRequest $request): RedirectResponse
    {
        BkuKontrak::create($request->validated());

        return redirect()->back()->with('success', 'Penetapan Kontrak ke BKU berhasil disimpan.');
    }

    public function destroy(BkuKontrak $bkuKontrak): RedirectResponse
    {
        $bkuKontrak->delete();

        return redirect()->back()->with('success', 'Penetapan BKU Kontrak berhasil dihapus.');
    }
}
