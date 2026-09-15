<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJustifikasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOperatorBku() || $this->user()?->isStafEsdm();
    }

    public function rules(): array
    {
        return [
            'laporan_harian_id' => ['required', 'exists:laporan_harian,id'],
            'alasan_revisi' => ['required', 'string', 'min:10'],
            'produksi_usulan' => ['required', 'numeric', 'min:0'],
            'lifting_usulan' => ['required', 'numeric', 'min:0'],
        ];
    }
}
