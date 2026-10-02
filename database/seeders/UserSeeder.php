<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrator Sistem',
                'username' => 'admin',
                'email' => 'admin@batuceper.go.id',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'is_active' => true,
                'nip' => '198501012010011001',
                'jabatan' => 'Pranata Komputer Ahli Muda',
            ],
            [
                'name' => 'Pengurus Barang (Operator)',
                'username' => 'operator',
                'email' => 'operator@batuceper.go.id',
                'password' => Hash::make('password'),
                'role' => UserRole::Operator,
                'is_active' => true,
                'nip' => '198803152012021003',
                'jabatan' => 'Pengurus Barang Pengguna',
            ],
            [
                'name' => 'Camat Batuceper (Pimpinan)',
                'username' => 'pimpinan',
                'email' => 'camat@batuceper.go.id',
                'password' => Hash::make('password'),
                'role' => UserRole::Pimpinan,
                'is_active' => true,
                'nip' => '197506121995031002',
                'jabatan' => 'Camat Batuceper',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['username' => $userData['username']],
                $userData
            );
        }
    }
}
