<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BkuKontrakFilter
{
    public function __construct(protected Request $request)
    {
    }

    public function apply(Builder $query): Builder
    {
        return $query
            ->when(
                $this->request->filled('search'),
                fn($q) => $q->whereHas('bku', fn($qb) =>
                    $qb->where('nama', 'like', '%' . $this->request->str('search')->trim()->value() . '%')
                )
            )
            ->when(
                $this->request->filled('kontrak_id') && is_numeric($this->request->kontrak_id),
                fn($q) => $q->where('kontrak_id', (int) $this->request->kontrak_id)
            );
    }
}