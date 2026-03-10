<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles only if they don't exist
        Role::firstOrCreate(['name' => 'client']);
        Role::firstOrCreate(['name' => 'professional']);
    }
}
