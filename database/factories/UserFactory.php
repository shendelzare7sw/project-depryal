<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake('id_ID')->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Operator,
            'is_active' => true,
            'nip' => fake()->numerify('19##########00#'),
            'jabatan' => 'Pengurus Barang',
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::Admin,
            'jabatan' => 'Administrator Sistem',
        ]);
    }

    public function operator(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::Operator,
            'jabatan' => 'Pengurus Barang',
        ]);
    }

    public function pimpinan(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::Pimpinan,
            'jabatan' => 'Camat Batuceper',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
