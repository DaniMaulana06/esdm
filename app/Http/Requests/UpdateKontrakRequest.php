<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKontrakRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStafEsdm() ?? false;
    }

    public function rules(): array
    {
        $kontrakId = $this->route('kontrak')?->id ?? $this->route('kontrak');

        return [
            'nama' => ['required', 'string', 'max:100', Rule::unique('kontrak', 'nama')->ignore($kontrakId)],
            'keterangan' => ['nullable', 'string'],
        ];
    }
}
