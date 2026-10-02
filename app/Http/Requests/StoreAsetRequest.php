<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\StatusAset;
use App\Models\Aset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreAsetRequest extends FormRequest
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
            'kategori_aset_id' => ['required', 'integer', 'exists:kategori_aset,id'],
            'kode_barang' => ['required', 'string', 'max:50'],
            'nup' => ['required', 'integer', 'min:1', $this->uniqueNup()],
            'nama_barang' => ['required', 'string', 'max:255'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'luas' => ['nullable', 'numeric', 'min:0'],
            'tanggal_perolehan' => ['required', 'date', 'before_or_equal:today'],
            'harga_satuan' => ['required', 'numeric', 'min:0'],
            'nilai_perolehan' => ['required', 'numeric', 'min:0'],
            'umur_ekonomis' => ['required', 'integer', 'min:0'],
            'akumulasi_penyusutan' => ['required', 'numeric', 'min:0'],
            'sisa_ueb' => ['required', 'integer', 'min:0', 'lte:umur_ekonomis'],
            'nilai_buku' => ['required', 'numeric', 'min:0'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(StatusAset::class)],
            'fotos' => ['nullable', 'array', 'max:5'],
            'fotos.*' => ['image', 'max:4096'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nup.unique' => 'Kombinasi kode barang dan NUP sudah terdaftar.',
            'tanggal_perolehan.before_or_equal' => 'Tanggal perolehan tidak boleh melewati hari ini.',
        ];
    }

    protected function uniqueNup(): Unique
    {
        $rule = Rule::unique('aset', 'nup')->where('kode_barang', (string) $this->input('kode_barang'));
        $aset = $this->route('aset');

        return $aset instanceof Aset ? $rule->ignore($aset->id) : $rule;
    }
}
