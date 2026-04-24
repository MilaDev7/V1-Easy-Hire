<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $monthlyPlans = [
            ['name' => 'Pro Starter', 'price' => 199, 'apply_limit_monthly' => 20],
            ['name' => 'Pro Growth', 'price' => 399, 'apply_limit_monthly' => 50],
        ];

        foreach ($monthlyPlans as $plan) {
            $exists = DB::table('plans')
                ->where('plan_scope', 'professional_monthly')
                ->where('name', $plan['name'])
                ->exists();

            if (! $exists) {
                DB::table('plans')->insert([
                    'name' => $plan['name'],
                    'price' => $plan['price'],
                    'plan_scope' => 'professional_monthly',
                    'job_posts_limit' => 0,
                    'direct_requests_limit' => 0,
                    'duration_days' => 30,
                    'apply_limit_monthly' => $plan['apply_limit_monthly'],
                    'extra_apply_quantity' => 0,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $extraPlans = [
            ['name' => 'Extra Apply Pack 10', 'price' => 99, 'extra_apply_quantity' => 10],
        ];

        foreach ($extraPlans as $plan) {
            $exists = DB::table('plans')
                ->where('plan_scope', 'professional_extra')
                ->where('name', $plan['name'])
                ->exists();

            if (! $exists) {
                DB::table('plans')->insert([
                    'name' => $plan['name'],
                    'price' => $plan['price'],
                    'plan_scope' => 'professional_extra',
                    'job_posts_limit' => 0,
                    'direct_requests_limit' => 0,
                    'duration_days' => 30,
                    'apply_limit_monthly' => 0,
                    'extra_apply_quantity' => $plan['extra_apply_quantity'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('plans')
            ->whereIn('name', [
                'Pro Starter',
                'Pro Growth',
                'Extra Apply Pack 10',
            ])
            ->whereIn('plan_scope', ['professional_monthly', 'professional_extra'])
            ->delete();
    }
};
