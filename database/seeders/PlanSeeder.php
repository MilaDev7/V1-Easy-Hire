<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
    Plan::updateOrCreate(
    ['name' => 'Basic'],
    ['price' => 100, 'job_posts_limit' => 5]
);

Plan::updateOrCreate(
    ['name' => 'Pro'],
    ['price' => 250, 'job_posts_limit' => 15]
);

Plan::updateOrCreate(
    ['name' => 'Premium'],
    ['price' => 500, 'job_posts_limit' => 50]
);

        
    }
}