<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\JobPost;

class SubscriptionController extends Controller
{
    public function buy($planId)
    {
        $plan = Plan::find($planId);

        if (!$plan) {
            return response()->json(['message' => 'Plan not found'], 404);
        }

        $user = auth()->user();

        // delete old subscription (simple version)
        Subscription::where('user_id', $user->id)->delete();

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active', // 🔥 ADD THIS
            'expires_at' => now()->addMonth()
        ]);

        return response()->json([
            'message' => 'Plan purchased successfully'
        ]);
    }

    //mySubscription

    public function mySubscription()
    {
        $user = auth()->user();

        $subscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('plan')
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'No active subscription'
            ], 404);
        }

        // count active jobs (ignore cancelled)
        $activeJobs = JobPost::where('client_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->count();

        $limit = $subscription->plan->job_posts_limit;

        $remaining = $limit - $activeJobs;

        return response()->json([
            'plan' => $subscription->plan->name,
            'job_limit' => $limit,
            'active_jobs' => $activeJobs,
            'remaining_jobs' => max($remaining, 0)
        ]);
    }
}