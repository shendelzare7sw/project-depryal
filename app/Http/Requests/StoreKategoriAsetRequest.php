<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\KategoriAset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKategoriAsetRequest extends FormRequest
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
        $kategori = $this->route('kategori_aset');

        return [
            'kode' => ['required', 'string', 'max:20', Rule::unique('kategori_aset', 'kode')->ignore($kategori instanceof KategoriAset ? $kategori->id : null)],
            'nama' => ['required', 'string', 'max:100'],
        ];
    }
}
