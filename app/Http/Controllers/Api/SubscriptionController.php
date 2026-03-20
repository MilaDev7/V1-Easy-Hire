<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Subscription;

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
            'remaining_posts' => $plan->job_posts_limit,
            'expires_at' => now()->addMonth()
        ]);

        return response()->json([
            'message' => 'Plan purchased successfully'
        ]);
    }
}