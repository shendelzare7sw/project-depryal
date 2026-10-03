<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StorePenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active'), 'email' => $this->input('email') ?: null]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $id = $user instanceof User ? $user->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['boolean'],
            'nip' => ['nullable', 'string', 'max:30'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'password' => $id ? ['prohibited'] : ['required', 'confirmed', Password::min(8)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['username.regex' => 'Username hanya boleh berisi huruf, angka, titik, strip, dan garis bawah (tanpa spasi).'];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'nama', 'role' => 'peran', 'is_active' => 'status aktif', 'nip' => 'NIP', 'jabatan' => 'jabatan', 'email' => 'email'];
    }
}
