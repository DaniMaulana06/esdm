<?php

namespace App\Enums;

enum KategoriBku: string
{
    case PERUSAHAAN = 'Perusahaan';
    case KOPERASI = 'Koperasi';
    case UMKM = 'UMKM';

    public static function values(): array {
        return array_column(self::cases(), 'value');
    }
}
