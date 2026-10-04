<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Ganti kata sandi wajib (login pertama / setelah reset Admin): tidak boleh sama dengan kata sandi sementara.
 */
class GantiPasswordRequest extends FormRequest
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
            'password' => ['required', 'confirmed', Password::min(8), function (string $attribute, mixed $value, Closure $fail): void {
                if (Hash::check((string) $value, (string) $this->user()?->password)) {
                    $fail('Kata sandi baru harus berbeda dari kata sandi sementara.');
                }
            }],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['password' => 'kata sandi baru'];
    }
}
