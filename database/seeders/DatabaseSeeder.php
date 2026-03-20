<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Group all your seeders here
        $this->call([
            PlanSeeder::class,
            RoleSeeder::class,
        ]);

        // ✅ FIX: Use updateOrCreate to avoid "Duplicate entry" errors
        $admin = User::updateOrCreate(
            ['email' => 'admin@test.com'], // The unique column to check
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Assign the Spatie Role
        // (Spatie handles this safely even if the role is already assigned)
        $admin->assignRole('admin');

        $this->command->info("Admin User Ready: admin@test.com / password");
         $this->call(PlanSeeder::class);
    }
}