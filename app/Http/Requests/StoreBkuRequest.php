<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBkuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStafEsdm() ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => [
                'required',
                'string',
                'max:100',
                'unique:bku,nama',
            ],
            'kota' => [
                'required',
                'string',
                'max:100',
            ],
            'penetapan' => [
                'required',
                'numeric',
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
            'kota.required' => 'Kabupaten / Kota wajib dipilih',
            'penetapan.required' => 'Jumlah penetapan wajib diisi',
            'penetapan.numeric' => 'Penetapan harus berupa angka',
            'penetapan.max' => 'Penetapan tidak bisa lebih dari 10.000',
            'penetapan.min' => 'Penetapan tidak boleh kurang dari 1',
        ];
    }
}
