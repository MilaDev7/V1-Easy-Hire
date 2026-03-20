<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the Roles first
        $this->call([
            RoleSeeder::class,
        ]);

        // 2. Create a default Admin User
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin', // Sets the column in users table
        ]);

        // 3. Assign the Spatie Role
        $admin->assignRole('admin');

        echo "Admin User Created: admin@test.com / password \n";
    }
}