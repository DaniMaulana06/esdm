<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBkuKontrakRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStafEsdm() ?? false;
    }

    public function rules(): array
    {
        return [
            'bku_id' => ['required', 'exists:bku,id'],
            'kontrak_id' => [
                'required',
                'exists:kontrak,id',
                Rule::unique('bku_kontrak')->where(function ($query) {
                    return $query->where('bku_id', $this->bku_id);
                }),
            ],
            'jumlah_sumur' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'kontrak_id.unique' => 'Kombinasi BKU dan Kontrak ini sudah ada.',
        ];
    }
}
