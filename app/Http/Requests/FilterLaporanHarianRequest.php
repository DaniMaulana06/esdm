<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterLaporanHarianRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'bku_id' => [
                'nullable',
                'integer',
                'exists:bku,id',
            ],

            'kontrak_id' => [
                'nullable',
                'integer',
                'exists:kontrak,id',
            ],

            'tanggal' => [
                'nullable',
                'date',
            ],

            'sort' => [
                'nullable',
                Rule::in([
                    'tanggal',
                    'total_produksi',
                    'total_lifting',
                ]),
            ],

            'direction' => [
                'nullable',
                Rule::in([
                    'asc',
                    'desc',
                ]),
            ],
        ];
    }
}
