<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessJustifikasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStafEsdm() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(['approved', 'rejected']),
            ],

            'catatan_dinas' => [
                'required',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' =>
                'Status pengajuan wajib dipilih.',

            'status.in' =>
                'Status pengajuan tidak valid.',

            'catatan_dinas.required' =>
                'Catatan wajib diisi.',

            'catatan_dinas.max' =>
                'Catatan dinas maksimal 1000 karakter.',
        ];
    }
}
