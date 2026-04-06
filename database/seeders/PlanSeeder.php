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
            ['price' => 100, 'job_posts_limit' => 5, 'direct_requests_limit' => 0, 'duration_days' => 30]
        );

        Plan::updateOrCreate(
            ['name' => 'Pro'],
            ['price' => 250, 'job_posts_limit' => 15, 'direct_requests_limit' => 3, 'duration_days' => 45]
        );

        Plan::updateOrCreate(
            ['name' => 'Premium'],
            ['price' => 500, 'job_posts_limit' => 50, 'direct_requests_limit' => 10, 'duration_days' => 60]
        );

    }
}
