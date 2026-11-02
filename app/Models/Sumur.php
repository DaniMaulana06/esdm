<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Sumur extends Model
{
    use HasFactory;

    protected $table = 'sumur';

    protected $fillable = [
        'bku_kontrak_id',
        'nama_sumur',
        'desa',
        'kecamatan',
        'kabupaten',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:9',
        'longitude' => 'decimal:8',
    ];

    public function bkuKontrak(): BelongsTo
    {
        return $this->belongsTo(BkuKontrak::class, 'bku_kontrak_id');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            // search nama sumur
            ->when(!empty($filters['search']), function (Builder $q) use ($filters) {
                $search = strtoupper(
                    preg_replace('/[^A-Za-z0-9]/', '', $filters['search'])
                );

                $q->whereRaw(
                    "REGEXP_REPLACE(UPPER(nama_sumur), '[^A-Z0-9]', '') LIKE ?",
                    ['%' . $search . '%']
                );
            })
            
            // Filter BKU
            ->when(!empty($filters['bku_id']), function (Builder $q) use ($filters) {
                $q->whereHas('bkuKontrak', function (Builder $subQuery) use ($filters) {
                    $subQuery->where('bku_id', $filters['bku_id']);
                });
            })

            // Filter Kontrak
            ->when(!empty($filters['kontrak_id']), function (Builder $q) use ($filters) {
                $q->whereHas('bkuKontrak', function (Builder $subQuery) use ($filters) {
                    $subQuery->where('kontrak_id', $filters['kontrak_id']);
                });
            });
    }
}
