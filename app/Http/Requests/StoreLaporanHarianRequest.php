<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLaporanHarianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOperatorBku() || $this->user()?->isStafEsdm();
    }

    public function rules(): array
    {
        return [
            'bku_kontrak_id' => ['required', 'exists:bku_kontrak,id'],
            'tanggal' => [
                'required',
                'date',
                Rule::unique('laporan_harian')->where(function ($query) {
                    return $query->where('bku_kontrak_id', $this->bku_kontrak_id);
                }),
            ],
            'total_produksi' => ['required', 'numeric', 'min:0'],
            'total_lifting' => ['required', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.unique' => 'Laporan harian untuk kontrak ini pada tanggal tersebut sudah pernah dibuat. Silakan ajukan Justifikasi Revisi jika ingin mengubah data.',
        ];
    }
}
