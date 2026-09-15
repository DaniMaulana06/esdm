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
            'nama' => ['required', 'string', 'max:100', 'unique:bku,nama'],
            'penetapan' => ['required', 'integer', 'min:1900', 'max:2100'],
        ];
    }
}
