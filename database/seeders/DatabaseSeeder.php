<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        User::updateOrCreate(
            ['email' => 'veterinarysuperadminoffice@gmail.com'],
            [
                'name' => 'CVO Super Admin',
                'password' => Hash::make('VetSAoffice@2026'),
                'role' => 'super_admin',
            ]
        );

        // 2. Standard Admin 1
        User::updateOrCreate(
            ['email' => 'veterinaryadmin1office@gmail.com'],
            [
                'name' => 'CVO Admin 1',
                'password' => Hash::make('VetA1office@2026'),
                'role' => 'admin',
            ]
        );

        // 3. Standard Admin 2
        User::updateOrCreate(
            ['email' => 'veterinaryadmin2office@gmail.com'],
            [
                'name' => 'CVO Admin 2',
                'password' => Hash::make('VetA2office@2026'),
                'role' => 'admin',
            ]
        );

        // 4. Legacy Veterinary Office Admin 1 (Synced with your controller check)
        User::updateOrCreate(
            ['email' => 'veterinaryadmin3office@gmail.com'],
            [
                'name' => 'CVO Admin 3',
                'password' => Hash::make('VetA3office@2026'),
                'role' => 'admin',
            ]
        );
    
    }
}
