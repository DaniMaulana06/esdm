<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'penetapan' => ['required', 'numeric', 'max:10000'],
        ];
    }

    public function messages(): array 
    {
        return [
            'nama.required' => 'Nama BKU wajib diisi',
            'nama.unique' => 'Nama BKU sudah ada',
            'penetapan.required' => 'Jumlah penetapan wajib diisi',
            'penetapan.numeric' => 'Penetapan harus berupa angka',
            'penetapan.max' => 'Penetapan tidak bisa lebih dari 10.000',
        ];    
    }
}
