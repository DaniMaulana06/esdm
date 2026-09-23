<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanHarian extends Model
{
    use HasFactory;

    protected $table = 'laporan_harian';

    protected $fillable = [
        'bku_kontrak_id',
        'tanggal',
        'total_produksi',
        'total_lifting',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_produksi' => 'decimal:2',
        'total_lifting' => 'decimal:2',
    ];

    public function bkuKontrak(): BelongsTo
    {
        return $this->belongsTo(BkuKontrak::class, 'bku_kontrak_id');
    }

    public function justifikasis(): HasMany
    {
        return $this->hasMany(Justifikasi::class, 'laporan_harian_id');
    }

    public function scopeForUser(
        Builder $query,
        User $user
    ): Builder {
        if ($user->isOperatorBku()) {
            $query->whereHas(
                'bkuKontrak',
                function (Builder $query) use ($user) {
                    $query->where(
                        'bku_id',
                        $user->bku_id
                    );
                }
            );
        }
    
        return $query;
    }
}
