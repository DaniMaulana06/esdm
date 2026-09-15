<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
