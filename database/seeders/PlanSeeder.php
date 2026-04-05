<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(
            ['name' => 'Basic'],
            ['price' => 100, 'job_posts_limit' => 5, 'duration_days' => 30]
        );

        Plan::updateOrCreate(
            ['name' => 'Pro'],
            ['price' => 250, 'job_posts_limit' => 15, 'duration_days' => 45]
        );

        Plan::updateOrCreate(
            ['name' => 'Premium'],
            ['price' => 500, 'job_posts_limit' => 50, 'duration_days' => 60]
        );

    }
}
