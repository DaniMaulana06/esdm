<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJustifikasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOperatorBku();
    }

    public function rules(): array
    {
        return [
            'laporan_harian_id' => [
                'required',
                'integer',
                'exists:laporan_harian,id',
            ],

            'alasan_revisi' => [
                'required',
                'string',
                'max:1000',
            ],

            'produksi_usulan' => [
                'required',
                'numeric',
                'min:0',
            ],

            'lifting_usulan' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'laporan_harian_id.required' =>
                'Laporan harian wajib dipilih.',

            'laporan_harian_id.exists' =>
                'Laporan harian tidak ditemukan.',

            'alasan_revisi.required' =>
                'Alasan revisi wajib diisi.',

            'alasan_revisi.max' =>
                'Alasan revisi maksimal 1000 karakter.',

            'produksi_usulan.required' =>
                'Produksi usulan wajib diisi.',

            'produksi_usulan.numeric' =>
                'Produksi usulan harus berupa angka.',

            'produksi_usulan.min' =>
                'Produksi usulan tidak boleh kurang dari 0.',

            'lifting_usulan.required' =>
                'Lifting usulan wajib diisi.',

            'lifting_usulan.numeric' =>
                'Lifting usulan harus berupa angka.',

            'lifting_usulan.min' =>
                'Lifting usulan tidak boleh kurang dari 0.',
        ];
    }
}
