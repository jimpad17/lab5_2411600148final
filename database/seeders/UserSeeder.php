<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Keep the Lab 4 credentials usable: ADMIN / ADMIN123.
     * Breeze logs in with the email field, so "ADMIN" is mapped in LoginRequest.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@jimtrack.local'],
            [
                'name' => 'ADMIN',
                'password' => Hash::make('ADMIN123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
