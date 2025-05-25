<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a user with the default password
        if (\App\Models\User::where('email', 'superadmin@gmail.com')->exists()) {
            return; // User already exists, no need to create again
        }
        \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => bcrypt('Password1234!'), // Use bcrypt to hash the password
            'email_verified_at' => now(), // Set email verification date to now
            'remember_token' => null, // Set remember token to null
            'created_at' => now(), // Set created at date to now
            'updated_at' => now(), // Set updated at date to now
            'role' => \App\Enums\RoleEnum::SUPERADMIN->value, // Set role to ADMIN
        ]);
    }
}
