<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TambahAsetPeriodeRequest extends FormRequest
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
            'aset_ids' => ['required', 'array', 'min:1'],
            'aset_ids.*' => ['integer', 'exists:aset,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['aset_ids.required' => 'Centang minimal satu aset yang akan ditambahkan.'];
    }
}
