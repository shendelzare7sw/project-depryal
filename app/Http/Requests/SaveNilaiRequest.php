<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Kriteria;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Nilai per kriteria aktif: boleh kosong (belum dinilai), bila diisi harus bilangan bulat dalam rentang skala kriteria.
 */
class SaveNilaiRequest extends FormRequest
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
        $rules = [
            'kriteria' => ['nullable', 'array'],
            'deskripsi_kondisi' => ['nullable', 'string', 'max:2000'],
            'fotos' => ['nullable', 'array', 'max:5'],
            'fotos.*' => ['image', 'max:4096'],
            'lanjut' => ['nullable', 'boolean'],
        ];

        foreach (Kriteria::where('is_active', true)->get() as $kriteria) {
            $rules["kriteria.{$kriteria->id}"] = ['nullable', 'integer', "between:{$kriteria->skala_min},{$kriteria->skala_maks}"];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return Kriteria::where('is_active', true)->get()
            ->mapWithKeys(fn (Kriteria $k) => ["kriteria.{$k->id}" => "nilai {$k->nama}"])->all();
    }
}
