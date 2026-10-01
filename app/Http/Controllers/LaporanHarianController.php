<?php

namespace App\Http\Controllers;

use App\Filters\LaporanHarianFilter;
use App\Http\Requests\FilterLaporanHarianRequest;
use App\Http\Requests\StoreLaporanHarianRequest;
use App\Models\BkuKontrak;
use App\Models\LaporanHarian;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LaporanHarianController extends Controller
{
    public function index(
        FilterLaporanHarianRequest $request,
        LaporanHarianFilter $filter
    ): Response {
        $user = $request->user();
        $validated = $request->validated();

        // 1) Query terfilter (aturan akses + search/filter dari kode lama)
        $filtered = LaporanHarian::query()->forUser($user);
        $filter->apply($filtered, $validated);

        // Hanya butuh ID-nya; buang orderBy bawaan filter karena sorting diatur di bawah
        $filteredIds = (clone $filtered)
            ->reorder()
            ->select('laporan_harian.id');

        // 2a) Sorting level kelompok (pakai alias agregat — hanya untuk query GROUP BY)
        $sortMap = [
            'tanggal'       => 'laporan_harian.tanggal',
            'total_produksi' => 'sum_produksi',
            'total_lifting'  => 'sum_lifting',
        ];
        $sort = $sortMap[$validated['sort'] ?? ''] ?? 'laporan_harian.tanggal';
        $direction = ($validated['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        // 2b) Sorting untuk query flat operator BKU (pakai nama kolom asli)
        $sortMapFlat = [
            'tanggal'       => 'laporan_harian.tanggal',
            'total_produksi' => 'laporan_harian.total_produksi',
            'total_lifting'  => 'laporan_harian.total_lifting',
        ];
        $sortFlat = $sortMapFlat[$validated['sort'] ?? ''] ?? 'laporan_harian.tanggal';

        // 3) Paginate kelompok: 1 baris = 1 BKU + 1 tanggal
        $groups = LaporanHarian::query()
            ->whereIn('laporan_harian.id', $filteredIds)
            ->join('bku_kontrak', 'bku_kontrak.id', '=', 'laporan_harian.bku_kontrak_id')
            ->select('bku_kontrak.bku_id', 'laporan_harian.tanggal')
            ->selectRaw('SUM(laporan_harian.total_produksi) as sum_produksi')
            ->selectRaw('SUM(laporan_harian.total_lifting) as sum_lifting')
            ->groupBy('bku_kontrak.bku_id', 'laporan_harian.tanggal')
            ->orderBy($sort, $direction)
            ->orderBy('bku_kontrak.bku_id')
            ->paginate(10)
            ->withQueryString();

        // 4) Ambil laporan milik kelompok di halaman ini (tetap mengikuti filter)
        $pairs = $groups->getCollection();

        $laporans = $pairs->isEmpty()
            ? collect()
            : LaporanHarian::query()
                ->with(['bkuKontrak.bku', 'bkuKontrak.kontrak', 'justifikasiTerbaru'])
                ->whereIn('laporan_harian.id', $filteredIds)
                ->where(function ($q) use ($pairs) {
                    foreach ($pairs as $p) {
                        $q->orWhere(function ($q) use ($p) {
                            $q->whereDate('tanggal', Carbon::parse($p->tanggal)->toDateString())
                                ->whereHas('bkuKontrak', fn($b) => $b->where('bku_id', $p->bku_id));
                        });
                    }
                })
                ->get()
                ->groupBy(fn($l) => $l->bkuKontrak->bku_id . '|' . Carbon::parse($l->tanggal)->toDateString());

        // 5) Bentuk akhir untuk Vue
        $groups->through(function ($p) use ($laporans) {
            $date = Carbon::parse($p->tanggal)->toDateString();
            $key = $p->bku_id . '|' . $date;
            $items = $laporans->get($key, collect())->values();

            return [
                'key' => $key,
                'bku' => $items->first()?->bkuKontrak->bku,
                'tanggal' => $date,
                'total_produksi' => $p->sum_produksi,
                'total_lifting' => $p->sum_lifting,
                'items' => $items,
            ];
        });

        // BKU + Kontrak untuk dropdown (tidak berubah)
        $bkuKontrakQuery = BkuKontrak::with(['bku', 'kontrak']);

        if ($user->isOperatorBku()) {
            $bkuKontrakQuery->where('bku_id', $user->bku_id);
        }

        // Query flat (per baris) khusus untuk operator BKU
        $laporanHarians = null;
        if ($user->isOperatorBku()) {
            $laporanHarians = LaporanHarian::query()
                ->with(['bkuKontrak.bku', 'bkuKontrak.kontrak', 'justifikasiTerbaru'])
                ->whereIn('laporan_harian.id', $filteredIds)
                ->orderBy($sortFlat, $direction)
                ->paginate(10)
                ->withQueryString();
        }

        return Inertia::render('LaporanHarian/Index', [
            'groups'          => $user->isOperatorBku() ? null : $groups,
            'laporanHarians'  => $laporanHarians,
            'bkuKontraks'     => $bkuKontrakQuery->get(),
            'filters'         => $validated,
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();

        $query = BkuKontrak::with([
            'bku:id,nama',
            'kontrak:id,nama',
        ]);

        // operator hanya melihat kontrak dari BKU miliknya
        if ($user->isOperatorBku()) {
            $query->where('bku_id', $user->bku_id);
        }

        $bkuKontraks = $query
            ->orderBy('bku_id')
            ->get();

        return Inertia::render('LaporanHarian/Create', [
            'bkuKontraks' => $bkuKontraks,
        ]);
    }

    public function edit(LaporanHarian $laporanHarian): Response
    {
        $laporanHarian->load([
            'bkuKontrak.bku',
            'bkuKontrak.kontrak',
        ]);

        return Inertia::render('LaporanHarian/Edit', [
            'laporanHarian' => $laporanHarian,
        ]);
    }

    public function store(StoreLaporanHarianRequest $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        $bkuKontraks = BkuKontrak::query()
            ->where('bku_id', $user->bku_id)
            ->pluck('id')
            ->sort()
            ->values();

        $submittedIds = collect($validated['laporan'])
            ->pluck('bku_kontrak_id')
            ->sort()
            ->values();

        //validasi agar seluruh kontrak dilaporakan
        if (
            $bkuKontraks->count() !== $submittedIds->count() ||
            $bkuKontraks->diff($submittedIds)->isNotEmpty() ||
            $submittedIds->diff($bkuKontraks)->isNotEmpty()
        ) {
            return back()
                ->withErrors([
                    'laporan' => 'Semua kontrak milik BKU wajib dilaporkan.',
                ])
                ->withInput();
        }

        foreach ($validated['laporan'] as $index => $laporan) { //validasi 1 hari 1 laporan
            $sudahAda = LaporanHarian::query()
                ->where('bku_kontrak_id', $laporan['bku_kontrak_id'])
                ->whereDate('tanggal', $validated['tanggal'])
                ->exists();

            if ($sudahAda) {
                return back()
                    ->withErrors([
                        "laporan.$index.bku_kontrak_id" =>
                            'Laporan untuk kontrak ini pada tanggal tersebut sudah pernah dibuat.',
                    ])
                    ->withInput();
            }
        }

        // simpan laporan dalam 1 transaksi
        DB::transaction(function () use ($validated) {
            foreach ($validated['laporan'] as $laporan) {
                LaporanHarian::create([
                    'bku_kontrak_id' => $laporan['bku_kontrak_id'],
                    'tanggal' => $validated['tanggal'],
                    'total_produksi' => $laporan['total_produksi'],
                    'total_lifting' => $laporan['total_lifting'],
                ]);
            }
        });

        return redirect()->route('laporan-harian.index')->with('success', 'Laporan produksi & lifting harian berhasil disimpan.');
    }

    public function update(
        Request $request,
        LaporanHarian $laporanHarian
    ): RedirectResponse {
        $validated = $request->validate([
            'total_produksi' => ['required', 'numeric', 'min:0'],
            'total_lifting' => ['required', 'numeric', 'min:0'],
        ]);

        $laporanHarian->update($validated);

        return redirect()
            ->route('laporan-harian.index')
            ->with('success', 'Laporan harian berhasil diperbarui.');
    }

    public function destroy(LaporanHarian $laporanHarian): RedirectResponse
    {
        $laporanHarian->delete();

        // Cache::forget('laporan-harian.all');

        return redirect()->back()->with('success', 'Data Kontrak berhasil dihapus.');
    }
}
