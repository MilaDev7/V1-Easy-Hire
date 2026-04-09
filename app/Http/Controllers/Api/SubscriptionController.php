<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Chapa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function buy($planId)
    {
        $plan = Plan::find($planId);

        if (! $plan) {
            return response()->json(['message' => 'Plan not found'], 404);
        }

        $user = Auth::user();

        // Use user name as tx_ref prefix (sanitized for gateway safety).
        $namePrefix = strtolower(trim((string) $user->name));
        $namePrefix = preg_replace('/[^a-z0-9]+/', '_', $namePrefix);
        $namePrefix = trim((string) $namePrefix, '_');
        if ($namePrefix === '') {
            $namePrefix = 'user_'.$user->id;
        }
        $txRef = Chapa::generateReference($namePrefix);
        $chapa = new Chapa;

        $email = trim($user->email);
        $firstName = trim($user->name);

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'message' => 'Invalid email address. Please update your profile email.',
            ], 400);
        }

        $response = $chapa->initializePayment([
            'amount' => $plan->price,
            'currency' => 'ETB',
            'email' => $email,
            'first_name' => $firstName,
            'tx_ref' => $txRef,
            // Must match an existing route.
            'callback_url' => url('/api/chapa/payment-success'),
            'return_url' => url('/payment-success?tx_ref='.$txRef),
            'meta' => [
                'plan_id' => $plan->id,
                'user_id' => $user->id,
            ],
        ]);

        if (! is_array($response) || ($response['status'] ?? null) !== 'success') {
            Log::error('Payment initialization failed', [
                'tx_ref' => $txRef,
                'response' => $response,
            ]);

            $errorMessage = $response['message'] ?? 'Payment initialization failed';

            if (isset($response['message']) && is_array($response['message'])) {
                $errorMessage = implode(', ', $response['message']);
            }

            return response()->json([
                'message' => $errorMessage,
            ], 500);
        }

        return response()->json([
            'redirect_url' => $response['data']['checkout_url'],
            'tx_ref' => $txRef,
        ]);
    }

    public function mySubscription()
    {
        $user = Auth::user();

        $subscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('plan')
            ->first();

        if (! $subscription) {
            return response()->json([
                'message' => 'No active subscription',
                'has_subscription' => false,
            ], 200);
        }

        $activeJobs = JobPost::where('client_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->count();

        $limit = $subscription->remaining_posts;
        $remaining = $limit - $activeJobs;

        return response()->json([
            'has_subscription' => true,
            'plan_name' => $subscription->plan->name,
            'plan' => $subscription->plan->name,
            'job_limit' => $limit,
            'active_jobs' => $activeJobs,
            'remaining_jobs' => max($remaining, 0),
            'direct_requests_limit' => $subscription->plan->direct_requests_limit ?? 0,
            'direct_requests_remaining' => $subscription->direct_requests_remaining ?? 0,
            'expires_at' => $subscription->expires_at,
            'duration_days' => $subscription->plan->duration_days ?? 30,
        ]);
    }
}
