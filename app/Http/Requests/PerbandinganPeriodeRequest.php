<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PerbandinganPeriodeRequest extends FormRequest
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
            'lama' => ['nullable', 'integer', 'exists:periode_penilaian,id', 'different:baru'],
            'baru' => ['nullable', 'integer', 'exists:periode_penilaian,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['lama.different' => 'Pilih dua periode yang berbeda untuk dibandingkan.'];
    }
}
