<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Adding guard_name 'web' ensures Spatie finds it correctly
        Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'professional', 'guard_name' => 'web']);
    }
}