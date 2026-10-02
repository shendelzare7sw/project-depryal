<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TindakanAset;
use App\Models\HasilMoora;
use App\Models\PeriodePenilaian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveKeputusanRequest extends FormRequest
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
            'tindakan' => ['required', Rule::enum(TindakanAset::class)],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Catatan wajib (min 10 karakter) bila keputusan berbeda dari rekomendasi sistem (aturan bisnis #6).
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $periode = $this->route('periode');
            $aset = $this->route('aset');

            if ($validator->errors()->has('tindakan') || ! $periode instanceof PeriodePenilaian) {
                return;
            }

            $rekomendasi = HasilMoora::where(['periode_id' => $periode->id, 'aset_id' => is_object($aset) ? $aset->getKey() : $aset])->first()?->rekomendasi;
            $beda = $rekomendasi !== null && $rekomendasi->value !== $this->input('tindakan');

            if ($beda && mb_strlen(trim((string) $this->input('catatan'))) < 10) {
                $validator->errors()->add('catatan', 'Catatan minimal 10 karakter wajib diisi karena keputusan berbeda dari rekomendasi sistem.');
            }
        }];
    }
}
