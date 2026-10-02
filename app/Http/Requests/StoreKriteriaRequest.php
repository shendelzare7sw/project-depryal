<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TipeKriteria;
use App\Models\Kriteria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreKriteriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $kriteria = $this->route('kriteria');

        return [
            'kode' => ['required', 'string', 'max:10', Rule::unique('kriteria', 'kode')->ignore($kriteria instanceof Kriteria ? $kriteria->id : null)],
            'nama' => ['required', 'string', 'max:100'],
            'tipe' => ['required', Rule::enum(TipeKriteria::class)],
            'bobot_persen' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'skala_min' => ['required', 'integer', 'min:1', 'max:4'],
            'skala_maks' => ['required', 'integer', 'max:5', 'gt:skala_min'],
            'urutan' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'skala' => ['array'],
            'skala.*.label' => ['nullable', 'string', 'max:50'],
            'skala.*.deskripsi' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Label rubrik wajib untuk setiap nilai di dalam rentang skala.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['skala_min', 'skala_maks'])) {
                return;
            }

            foreach (range((int) $this->input('skala_min'), (int) $this->input('skala_maks')) as $nilai) {
                if (blank($this->input("skala.{$nilai}.label"))) {
                    $validator->errors()->add("skala.{$nilai}.label", "Label skala nilai {$nilai} wajib diisi.");
                }
            }
        }];
    }
}
