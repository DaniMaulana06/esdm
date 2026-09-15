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
            'nama' => ['required', 'string', 'max:100', Rule::unique('bku', 'nama')->ignore($bkuId)],
            'penetapan' => ['required', 'integer', 'min:1900', 'max:2100'],
        ];
    }
}
