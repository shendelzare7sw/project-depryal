<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Validation\Validator;

/**
 * Sama seperti StorePenggunaRequest (password diubah lewat reset), plus: admin tidak boleh
 * menurunkan role atau menonaktifkan akunnya sendiri.
 */
class UpdatePenggunaRequest extends StorePenggunaRequest
{
    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $target = $this->route('user');

            if (! $target instanceof User || ! $target->is($this->user())) {
                return;
            }

            if ($this->input('role') !== UserRole::Admin->value) {
                $validator->errors()->add('role', 'Anda tidak dapat mengubah peran akun Anda sendiri.');
            }

            if (! $this->boolean('is_active')) {
                $validator->errors()->add('is_active', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            }
        }];
    }
}
