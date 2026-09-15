<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Justifikasi extends Model
{
    use HasFactory;

    protected $table = 'justifikasi';

    protected $fillable = [
        'laporan_harian_id',
        'bku_id',
        'alasan_revisi',
        'produksi_usulan',
        'lifting_usulan',
        'status',
        'ditinjau_oleh',
        'catatan_dinas',
    ];

    protected $casts = [
        'produksi_usulan' => 'decimal:2',
        'lifting_usulan' => 'decimal:2',
    ];

    public function laporanHarian(): BelongsTo
    {
        return $this->belongsTo(LaporanHarian::class, 'laporan_harian_id');
    }

    public function bku(): BelongsTo
    {
        return $this->belongsTo(Bku::class, 'bku_id');
    }

    public function peninjau(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditinjau_oleh');
    }
}
