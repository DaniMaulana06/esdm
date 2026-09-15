<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSumurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStafEsdm() ?? false;
    }

    public function rules(): array
    {
        $sumurId = $this->route('sumur')?->id ?? $this->route('sumur');

        return [
            'bku_kontrak_id' => ['required', 'exists:bku_kontrak,id'],
            'nama_sumur' => ['required', 'string', 'max:100', Rule::unique('sumur', 'nama_sumur')->ignore($sumurId)],
            'desa' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kabupaten' => ['required', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
