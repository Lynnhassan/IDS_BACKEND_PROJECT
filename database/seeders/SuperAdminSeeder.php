<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@site.com'],
            [
                'fullName' => 'Super Admin',
                'password' => Hash::make('Admin12345'),
                'role' => 'SuperAdmin',
                'isActive' => true,
            ]
        );
    }
}
