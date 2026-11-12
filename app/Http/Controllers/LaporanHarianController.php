<?php

namespace App\Http\Controllers;

use App\Filters\LaporanHarianFilter;
use App\Http\Requests\FilterLaporanHarianRequest;
use App\Http\Requests\StoreLaporanHarianRequest;
use App\Models\BkuKontrak;
use App\Models\LaporanHarian;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class LaporanHarianController extends Controller
{
    private const WITH_RELATIONS = [
        'bkuKontrak:id,bku_id,kontrak_id',
        'bkuKontrak.bku:id,nama',
        'bkuKontrak.kontrak:id,nama',
        'justifikasiTerbaru',
    ];

    public function index(FilterLaporanHarianRequest $request, LaporanHarianFilter $filter): Response
    {
        $user = $request->user();
        $validated = $request->validated();
        $isOperator = $user->isOperatorBku();

        $filteredIds = $this->filteredIds($user, $filter, $validated);
        $direction = ($validated['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return Inertia::render('LaporanHarian/Index', [
            'groups' => $isOperator ? null : $this->groupedPage($filteredIds, $validated['sort'] ?? null, $direction),
            'laporanHarians' => $isOperator ? $this->flatPage($filteredIds, $validated['sort'] ?? null, $direction) : null,
            'bkuKontraks' => $this->bkuKontraksForDropdown($user),
            'filters' => $validated,
        ]);
    }

    /** Subquery ID laporan yang lolos aturan akses + filter. */
    private function filteredIds($user, LaporanHarianFilter $filter, array $validated): Builder
    {
        $query = LaporanHarian::query()->forUser($user);
        $filter->apply($query, $validated);

        return $query->reorder()->select('laporan_harian.id');
    }

    /** Non-operator: 1 baris = 1 BKU + 1 tanggal, beserta item kontraknya. */
    private function groupedPage(Builder $filteredIds, ?string $sortKey, string $direction)
    {
        $sort = [
            'tanggal' => 'laporan_harian.tanggal',
            'total_produksi' => 'sum_produksi',
            'total_lifting' => 'sum_lifting',
        ][$sortKey] ?? 'laporan_harian.tanggal';

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

        $itemsByGroup = $this->itemsForGroups($filteredIds, $groups->getCollection());

        return $groups->through(function ($p) use ($itemsByGroup) {
            $date = Carbon::parse($p->tanggal)->toDateString();
            $key = $p->bku_id . '|' . $date;
            $items = $itemsByGroup->get($key, collect())->values();

            return [
                'key' => $key,
                'bku' => $items->first()?->bkuKontrak->bku,
                'tanggal' => $date,
                'total_produksi' => $p->sum_produksi,
                'total_lifting' => $p->sum_lifting,
                'items' => $items,
            ];
        });
    }

    /** Ambil laporan untuk semua grup di halaman ini dalam 1 query. */
    private function itemsForGroups(Builder $filteredIds, Collection $pairs): Collection
    {
        if ($pairs->isEmpty()) {
            return collect();
        }

        $bkuIds = $pairs->pluck('bku_id')->unique()->values();
        $dates = $pairs->map(fn($p) => Carbon::parse($p->tanggal)->toDateString())->unique()->values();

        return LaporanHarian::query()
            ->select('laporan_harian.*')
            ->join('bku_kontrak', 'bku_kontrak.id', '=', 'laporan_harian.bku_kontrak_id')
            ->with(self::WITH_RELATIONS)
            ->whereIn('laporan_harian.id', $filteredIds)
            ->whereIn('bku_kontrak.bku_id', $bkuIds)
            ->whereIn('laporan_harian.tanggal', $dates)
            ->get()
            ->groupBy(fn($l) => $l->bkuKontrak->bku_id . '|' . Carbon::parse($l->tanggal)->toDateString());
    }

    /** Operator BKU: daftar flat per baris. */
    private function flatPage(Builder $filteredIds, ?string $sortKey, string $direction)
    {
        $sort = [
            'tanggal' => 'laporan_harian.tanggal',
            'total_produksi' => 'laporan_harian.total_produksi',
            'total_lifting' => 'laporan_harian.total_lifting',
        ][$sortKey] ?? 'laporan_harian.tanggal';

        return LaporanHarian::query()
            ->with(self::WITH_RELATIONS)
            ->whereIn('laporan_harian.id', $filteredIds)
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();
    }

    private function bkuKontraksForDropdown($user): Collection
    {
        return BkuKontrak::with(['bku:id,nama', 'kontrak:id,nama'])
            ->when($user->isOperatorBku(), fn($q) => $q->where('bku_id', $user->bku_id))
            ->get();
    }

    public function create(Request $request): Response
    {
        $user = $request->user();

        $query = BkuKontrak::with([
            'bku:id,nama',
            'kontrak:id,nama',
        ])->whereDate('created_at', '<', now()->toDateString());

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

    /**
     * Form create laporan untuk tanggal yang terlewat (backfill).
     * Hanya operator BKU yang dapat mengaksesnya.
     */
    public function createBackfill(Request $request): Response
    {
        $user = $request->user();

        $tanggal = $request->query('tanggal');

        // Validasi tanggal: wajib ada, valid, bukan hari ini, bukan masa depan
        if (!$tanggal || !strtotime($tanggal)) {
            abort(400, 'Tanggal tidak valid.');
        }

        $tgl = Carbon::parse($tanggal)->startOfDay();
        $today = Carbon::today();

        if ($tgl->gte($today)) {
            abort(400, 'Backfill hanya untuk tanggal sebelum hari ini.');
        }

        $query = BkuKontrak::with([
            'bku:id,nama',
            'kontrak:id,nama',
        ])
            ->where('bku_id', $user->bku_id)
            ->whereDate('created_at', '<', $tgl);

        $bkuKontraks = $query->orderBy('bku_id')->get();

        return Inertia::render('LaporanHarian/CreateBackfill', [
            'bkuKontraks' => $bkuKontraks,
            'tanggalBackfill' => $tgl->toDateString(),
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
            ->whereDate('created_at', '<', $validated['tanggal'])
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
                    'keterangan' => $laporan['keterangan'] ?? null,
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
            'keterangan' => ['nullable', 'string', 'max:100']
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
