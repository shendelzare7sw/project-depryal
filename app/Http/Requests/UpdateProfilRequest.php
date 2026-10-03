<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Ubah nama/email sendiri; ganti password wajib verifikasi password lama.
 */
class UpdateProfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => $this->input('email') ?: null]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()?->id)],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'nama', 'current_password' => 'kata sandi saat ini', 'password' => 'kata sandi baru'];
    }

    /**
     * @return array<string, string|null>
     */
    public function dataProfil(): array
    {
        $data = $this->safe()->only(['name', 'email']);

        if (filled($this->validated('password'))) {
            $data['password'] = $this->validated('password');
        }

        return $data;
    }
}
