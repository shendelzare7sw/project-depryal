<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\FormatLaporan;
use App\Enums\JenisLaporan;
use App\Enums\StatusPeriode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLaporanRequest extends FormRequest
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
            'periode_id' => ['required', 'integer', Rule::exists('periode_penilaian', 'id')
                ->whereIn('status', [StatusPeriode::Dihitung->value, StatusPeriode::Final->value])],
            'jenis' => ['required', Rule::enum(JenisLaporan::class)],
            'format' => ['required', Rule::enum(FormatLaporan::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['periode_id.exists' => 'Pilih periode yang sudah dihitung atau final.'];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['periode_id' => 'periode', 'jenis' => 'jenis laporan', 'format' => 'format'];
    }
}
