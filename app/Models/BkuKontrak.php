<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BkuKontrak extends Model
{
    use HasFactory;

    protected $table = 'bku_kontrak';

    protected $fillable = [
        'bku_id',
        'kontrak_id',
        'jumlah_sumur',
    ];

    public function bku(): BelongsTo
    {
        return $this->belongsTo(Bku::class, 'bku_id');
    }

    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(Kontrak::class, 'kontrak_id');
    }

    public function sumurs(): HasMany
    {
        return $this->hasMany(Sumur::class, 'bku_kontrak_id');
    }

    public function laporanHarians(): HasMany
    {
        return $this->hasMany(LaporanHarian::class, 'bku_kontrak_id');
    }
}
