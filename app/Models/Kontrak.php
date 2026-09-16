<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kontrak extends Model
{
    use HasFactory;

    protected $table = 'kontrak';

    protected $fillable = [
        'nama',
        'keterangan',
    ];

    public function bkus(): BelongsToMany
    {
        return $this->belongsToMany(Bku::class, 'bku_kontrak', 'kontrak_id', 'bku_id')
            ->withPivot('id', 'jumlah_sumur')
            ->withTimestamps();
    }

    public function bkuKontraks(): HasMany
    {
        return $this->hasMany(BkuKontrak::class, 'kontrak_id');
    }
}
