<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKontrakRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStafEsdm() ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100', 'unique:kontrak,nama'],
            'keterangan' => ['nullable', 'string'],
        ];
    }
}
