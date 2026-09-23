<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class LaporanHarianFilter
{
    private array $allowedSorts = [
        'tanggal',
        'total_produksi',
        'total_lifting',
    ];

    public function apply(
        Builder $query,
        array $filters
    ): Builder {
        $this->search($query, $filters);
        $this->filterBku($query, $filters);
        $this->filterTanggal($query, $filters);
        $this->sort($query, $filters);

        return $query;
    }

    private function search(
        Builder $query,
        array $filters
    ): void {
        $search = $filters['search'] ?? null;

        if (!$search) {
            return;
        }

        $query->where(function (Builder $query) use ($search) {
            $query
                ->whereHas(
                    'bkuKontrak.bku',
                    function (Builder $query) use ($search) {
                        $query->where(
                            'nama',
                            'like',
                            "%{$search}%"
                        );
                    }
                )
                ->orWhereHas(
                    'bkuKontrak.kontrak',
                    function (Builder $query) use ($search) {
                        $query->where(
                            'nama',
                            'like',
                            "%{$search}%"
                        );
                    }
                );
        });
    }

    private function filterBku(
        Builder $query,
        array $filters
    ): void {
        $bkuId = $filters['bku_id'] ?? null;

        if (!$bkuId) {
            return;
        }

        $query->whereHas(
            'bkuKontrak',
            function (Builder $query) use ($bkuId) {
                $query->where('bku_id', $bkuId);
            }
        );
    }

    private function filterTanggal(
        Builder $query,
        array $filters
    ): void {
        $tanggal = $filters['tanggal'] ?? null;

        if (!$tanggal) {
            return;
        }

        $query->whereDate('tanggal', $tanggal);
    }

    private function sort(
        Builder $query,
        array $filters
    ): void {
        $sort = $filters['sort'] ?? 'tanggal';
        $direction = $filters['direction'] ?? 'desc';

        if (!in_array($sort, $this->allowedSorts, true)) {
            $sort = 'tanggal';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $query->orderBy($sort, $direction);
    }
}