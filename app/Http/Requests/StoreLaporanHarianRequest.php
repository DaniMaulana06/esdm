<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLaporanHarianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOperatorBku() || $this->user()?->isStafEsdm();
    }

    // public function rules(): array
    // {
    //     return [
    //         'bku_kontrak_id' => ['required', 'exists:bku_kontrak,id'],
    //         'tanggal' => [
    //             'required',
    //             'date',
    //             Rule::unique('laporan_harian')->where(function ($query) {
    //                 return $query->where('bku_kontrak_id', $this->bku_kontrak_id);
    //             }),
    //         ],
    //         'total_produksi' => ['required', 'numeric', 'min:0'],
    //         'total_lifting' => ['required', 'numeric', 'min:0'],
    //         'keterangan' => ['nullable', 'string'],
    //     ];
    // }


    public function rules(): array
    {
        return [
            'tanggal' => [
                'required',
                'date',
                Rule::unique('laporan_harian')->where(function ($query) {
                    return $query->where('bku_kontrak_id', $this->bku_kontrak_id);
                }),
            ],

            'laporan' => [
                'required',
                'array',
                'min:1',
            ],

            'laporan.*.bku_kontrak_id' => [
                'required',
                'integer',
                'distinct',
            ],

            'laporan.*.total_produksi' => [
                'required',
                'numeric',
                'min:0',
            ],

            'laporan.*.total_lifting' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal laporan wajib diisi.',
            'tanggal.date' => 'Tanggal laporan tidak valid.',

            'laporan.required' => 'Data laporan wajib diisi.',
            'laporan.array' => 'Format data laporan tidak valid.',
            'laporan.min' => 'Minimal satu laporan harus diisi.',

            'laporan.*.bku_kontrak_id.required' =>
                'Kontrak wajib dipilih.',

            'laporan.*.bku_kontrak_id.integer' =>
                'Kontrak tidak valid.',

            'laporan.*.bku_kontrak_id.distinct' =>
                'Kontrak tidak boleh dimasukkan lebih dari satu kali.',

            'laporan.*.bku_kontrak_id.unique' =>
                'Laporan untuk kontrak ini pada tanggal tersebut sudah pernah dibuat.',

            'laporan.*.total_produksi.required' =>
                'Total produksi wajib diisi.',

            'laporan.*.total_produksi.numeric' =>
                'Total produksi harus berupa angka.',

            'laporan.*.total_produksi.min' =>
                'Total produksi tidak boleh kurang dari 0.',

            'laporan.*.total_lifting.required' =>
                'Total lifting wajib diisi.',

            'laporan.*.total_lifting.numeric' =>
                'Total lifting harus berupa angka.',

            'laporan.*.total_lifting.min' =>
                'Total lifting tidak boleh kurang dari 0.',
        ];
    }
}
