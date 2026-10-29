<?php

namespace App\Models;

use App\Enums\KategoriBku;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Bku extends Model
{
    use HasFactory;

    protected $table = 'bku';

    protected $fillable = [
        'nama',
        'kota',
        'kategori',
        'penetapan',
    ];

    protected $casts = [
        'kategori' => KategoriBku::class,
    ];

    public function kontraks(): BelongsToMany
    {
        return $this->belongsToMany(Kontrak::class, 'bku_kontrak', 'bku_id', 'kontrak_id')
            ->withPivot('id', 'jumlah_sumur')
            ->withTimestamps();
    }

    public function bkuKontraks(): HasMany
    {
        return $this->hasMany(BkuKontrak::class, 'bku_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'bku_id');
    }
}
