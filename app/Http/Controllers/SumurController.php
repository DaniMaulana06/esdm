<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSumurRequest;
use App\Http\Requests\UpdateSumurRequest;
use App\Models\BkuKontrak;
use App\Models\Sumur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class SumurController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

            $query = Sumur::with([
                'bkuKontrak.bku:id,nama',
                'bkuKontrak.kontrak:id,nama',
            ]);

            // Operator hanya boleh melihat sumur milik BKU-nya
            if ($user->isOperatorBku()) {
                $query->whereHas('bkuKontrak', function ($q) use ($user) {
                    $q->where('bku_id', $user->bku_id);
                });
        }

        $sumurs = $query->latest()->get();

        // return Inertia::render('Sumur/Index', [
        //     'sumurs' => $sumurs,
        // ]);

        return Inertia::render('Sumur/Index', [
            'sumurs' => $sumurs,
            'debugUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'bku_id' => $user->bku_id,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();

        $query = BkuKontrak::with([
            'bku:id,nama',
            'kontrak:id,nama',
        ]);

        // bku hanya mendapatkan pilihan kontrak dari bku nya sendiri
        if ($user->isOperatorBku()) {
            $query->where('bku_id', $user->bku_id);
        }

        $bkuKontraks = $query->get();

        return Inertia::render('Sumur/Create', [
            'bkuKontraks' => $bkuKontraks,
        ]);
    }

    public function store(StoreSumurRequest $request): RedirectResponse
    {
        $user = $request->user();

        $bkuKontrak = BkuKontrak::findOrFail(
            $request->bku_kontrak_id
        );

        // bku hanya boleh create sumur pada BKU miliknya
        if (
            $user->isOperatorBku() &&
            $bkuKontrak->bku_id != $user->bku_id
        ) {
            abort(403, 'Anda tidak memiliki izin untuk menambahkan sumur pada BKU ini.');
        }

        Sumur::create($request->validated());

        return redirect()
            ->route('sumur.index')
            ->with('success', 'Data Sumur berhasil ditambahkan.');
    }

    public function edit(
        Sumur $sumur,
        Request $request
    ): Response {
        $user = $request->user();

        // bku hanya bisa membuka sumur milik BKU-nya
        if (
            $user->isOperatorBku() &&
            $sumur->bkuKontrak->bku_id != $user->bku_id
        ) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit sumur ini.');
        }

        // bku hanya mendapatkan daftar BKU Kontrak miliknya
        $query = BkuKontrak::with([
            'bku:id,nama',
            'kontrak:id,nama',
        ])->orderBy('id');

        if ($user->isOperatorBku()) {
            $query->where('bku_id', $user->bku_id);
        }

        $bkuKontraks = $query->get();

        return Inertia::render('Sumur/Edit', [
            'sumur' => $sumur,
            'bkuKontraks' => $bkuKontraks,
        ]);
    }

    public function update(
        UpdateSumurRequest $request,
        Sumur $sumur
    ): RedirectResponse {
        $user = $request->user();

        //validasi sumur yang diedit memang milik BKU user yang sedang login
        if (
            $user->isOperatorBku() &&
            $sumur->bkuKontrak->bku_id != $user->bku_id
        ) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit sumur ini.');
        }

        //Ambil BKU Kontrak baru
        $bkuKontrak = BkuKontrak::findOrFail(
            $request->bku_kontrak_id
        );

        //validasi bku agar tidak update sumur bku lain
        if ($user->isOperatorBku() && $bkuKontrak->bku_id != $user->bku_id) {
            abort(403, 'Anda tidak memiliki izin untuk memindahkan sumur ke BKU ini.');
        }

        $sumur->update($request->validated());

        return redirect()
            ->route('sumur.index')
            ->with('success', 'Data Sumur berhasil diperbarui.');
    }

    public function destroy(
        Sumur $sumur,
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        // Pastikan sumur milik BKU operator
        if (
            $user->isOperatorBku() &&
            $sumur->bkuKontrak->bku_id != $user->bku_id
        ) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus sumur ini.');
        }

        $sumur->delete();

        return redirect()
            ->back()
            ->with('success', 'Data Sumur berhasil dihapus.');
    }
}