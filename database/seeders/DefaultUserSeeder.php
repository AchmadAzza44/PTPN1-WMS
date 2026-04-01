<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@ptpn1.co.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Operator
        User::updateOrCreate(
            ['email' => 'operator@ptpn1.co.id'],
            [
                'name' => 'Petugas Gudang',
                'password' => Hash::make('password'),
                'role' => 'operator',
            ]
        );

        // Manager
        User::updateOrCreate(
            ['email' => 'manager@ptpn1.co.id'],
            [
                'name' => 'Manager Logistik',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ]
        );
    }
}
