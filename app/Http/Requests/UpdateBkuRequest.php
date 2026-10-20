<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBkuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStafEsdm() ?? false;
    }

    public function rules(): array
    {
        $bkuId = $this->route('bku')?->id ?? $this->route('bku');

        return [
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('bku', 'nama')->ignore($bkuId)
            ],
            'kota' => [
                'required',
                'string',
                'max:100',
            ],
            'penetapan' => [
                'required',
                'integer',
                'max:10000',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama BKU wajib diisi',
            'nama.unique' => 'Nama BKU sudah ada',
            'kota.required' => 'Kabupaten / Kota wajib diisi',
            'kota.string' => 'Kota hanya dapat berupa huruf',
            'penetapan.required' => 'Jumlah penetapan wajib diisi',
            'penetapan.numeric' => 'Penetapan harus berupa angka',
            'penetapan.max' => 'Penetapan tidak bisa lebih dari 10.000',
            'penetapan.min' => 'Penetapan tidak boleh kurang dari 1',
        ];
    }
}
