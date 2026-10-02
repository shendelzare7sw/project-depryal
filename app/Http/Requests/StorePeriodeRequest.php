<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePeriodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'cakupan' => ['required', 'in:semua,kategori,perhatian,pilih'],
            'kategori_ids' => ['required_if:cakupan,kategori', 'array'],
            'kategori_ids.*' => ['integer', 'exists:kategori_aset,id'],
            'aset_ids' => ['required_if:cakupan,pilih', 'array'],
            'aset_ids.*' => ['integer', 'exists:aset,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kategori_ids.required_if' => 'Pilih minimal satu kategori aset.',
            'aset_ids.required_if' => 'Pilih minimal dua aset.',
        ];
    }
}
