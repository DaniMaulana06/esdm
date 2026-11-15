<?php
namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BkuFilter
{
    public function __construct(protected Request $request)
    {
    }

    public function apply(Builder $query): Builder
    {
        return $query
            ->when(
                $this->request->filled('search'),
                function (Builder $q) {
                    $search = '%' . $this->request->str('search')->trim()->value() . '%';
                    $q->where(function (Builder $subQuery) use ($search) {
                        $subQuery->where('nama', 'like', $search)->orWhere('kota', 'like', $search);
                    });
                }
            )
            ->when(
                $this->request->filled('kategori'),
                fn($q) => $q->where(
                    'kategori',
                    $this->request->str('kategori')->trim()->value()
                )
            );
    }
}