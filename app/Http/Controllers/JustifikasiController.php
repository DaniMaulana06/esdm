<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessJustifikasiRequest;
use App\Http\Requests\StoreJustifikasiRequest;
use App\Models\AuditLogs;
use App\Models\Justifikasi;
use App\Models\LaporanHarian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class JustifikasiController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Justifikasi::with([
            'laporanHarian.bkuKontrak.bku',
            'laporanHarian.bkuKontrak.kontrak',
            'bku',
            'peninjau',
        ]);

        if ($user->isOperatorBku()) {
            $query->where('bku_id', $user->bku_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $justifikasis = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Justifikasi/Index', [
            'justifikasis' => $justifikasis,
            'filters' => $request->only(['status']),
        ]);
    }

    public function store(
        StoreJustifikasiRequest $request
    ): RedirectResponse {
        $user = $request->user();

        $laporanHarian = LaporanHarian::with('bkuKontrak')
            ->whereHas(
                'bkuKontrak',
                function ($query) use ($user) {
                    $query->where('bku_id', $user->bku_id);
                }
            )
            ->findOrFail($request->laporan_harian_id);

        $sudahAda = Justifikasi::query()
            ->where('laporan_harian_id', $laporanHarian->id)
            ->where('status', 'pending')
            ->exists();

        if ($sudahAda) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Laporan ini masih memiliki pengajuan justifikasi yang sedang diproses.'
                );
        }

        Justifikasi::create([
            'laporan_harian_id' => $laporanHarian->id,
            'bku_id' => $user->bku_id,
            'alasan_revisi' => $request->alasan_revisi,
            'produksi_usulan' => $request->produksi_usulan,
            'lifting_usulan' => $request->lifting_usulan,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('laporan-harian.index')
            ->with(
                'success',
                'Pengajuan Justifikasi Revisi berhasil dikirim.'
            );
    }

    public function process(
        ProcessJustifikasiRequest $request,
        Justifikasi $justifikasi
    ): RedirectResponse {
        if ($justifikasi->status !== 'pending') {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Pengajuan Justifikasi ini sudah diproses sebelumnya.'
                );
        }

        DB::transaction(function () use ($request, $justifikasi) {
            $status = $request->status;

            if ($status === 'approved') {
                $laporanHarian = $justifikasi->laporanHarian;

                $oldValues = [
                    'total_produksi' =>
                        $laporanHarian->total_produksi,

                    'total_lifting' =>
                        $laporanHarian->total_lifting,
                ];

                $newValues = [
                    'total_produksi' =>
                        $justifikasi->produksi_usulan,

                    'total_lifting' =>
                        $justifikasi->lifting_usulan,
                ];

                $laporanHarian->update([
                    'total_produksi' =>
                        $justifikasi->produksi_usulan,

                    'total_lifting' =>
                        $justifikasi->lifting_usulan,
                ]);

                AuditLogs::create([
                    'user_id' => $request->user()->id,
                    'action' => 'APPROVE_REVISI_LAPORAN_HARIAN',
                    'auditable_type' => LaporanHarian::class,
                    'auditable_id' => $laporanHarian->id,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            $justifikasi->update([
                'status' => $status,
                'ditinjau_oleh' => $request->user()->id,
                'catatan_dinas' => $request->catatan_dinas,
            ]);
        });

        $message = $request->status === 'approved'
            ? 'Pengajuan Justifikasi Revisi telah disetujui dan data laporan harian telah diperbarui.'
            : 'Pengajuan Justifikasi Revisi telah ditolak.';

        return redirect()
            ->back()
            ->with('success', $message);
    }
}
