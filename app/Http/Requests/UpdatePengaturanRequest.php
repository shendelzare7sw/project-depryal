<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengaturanRequest extends FormRequest
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
            'ambang_pertahankan' => ['required', 'numeric', 'between:0,100', 'decimal:0,2', 'gt:ambang_perbaiki'],
            'ambang_perbaiki' => ['required', 'numeric', 'between:0,100', 'decimal:0,2'],
            'nama_instansi' => ['required', 'string', 'max:150'],
            'alamat_instansi' => ['nullable', 'string', 'max:255'],
            'nama_penandatangan' => ['required', 'string', 'max:150'],
            'nip_penandatangan' => ['nullable', 'string', 'max:30'],
            'jabatan_penandatangan' => ['required', 'string', 'max:100'],
            'batas_idle_menit' => ['required', 'integer', 'in:0,15,30,60,120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['ambang_pertahankan.gt' => 'Ambang Pertahankan harus lebih besar dari ambang Perbaiki/Hapus.'];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ambang_pertahankan' => 'ambang Pertahankan', 'ambang_perbaiki' => 'ambang Perbaiki/Hapus',
            'nama_instansi' => 'nama instansi', 'alamat_instansi' => 'alamat instansi', 'nama_penandatangan' => 'nama penandatangan',
            'nip_penandatangan' => 'NIP penandatangan', 'jabatan_penandatangan' => 'jabatan penandatangan', 'batas_idle_menit' => 'logout otomatis',
        ];
    }
}
