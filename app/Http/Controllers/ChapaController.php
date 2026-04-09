<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Chapa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChapaController extends Controller
{
    public function initializePayment(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $plan = Plan::findOrFail($request->plan_id);

        $txRef = Chapa::generateReference();
        $chapa = new Chapa;

        $response = $chapa->initializePayment([
            'amount' => $plan->price,
            'currency' => 'ETB',
            'email' => $user->email,
            'first_name' => $user->name,
            'tx_ref' => $txRef,
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

            return response()->json([
                'message' => $response['message'] ?? 'Payment initialization failed',
            ], 500);
        }

        return response()->json([
            'redirect_url' => $response['data']['checkout_url'],
            'tx_ref' => $txRef,
        ]);
    }

    public function verifyPayment(Request $request)
    {
        $txRef = $request->query('tx_ref');

        Log::info('verifyPayment API called', ['tx_ref' => $txRef, 'full_url' => $request->fullUrl()]);

        if (! $txRef) {
            return response()->json(['message' => 'Transaction reference required'], 400);
        }

        $chapa = new Chapa;
        $verification = $chapa->verifyTransaction($txRef);

        Log::info('Chapa verification response', [
            'tx_ref' => $txRef,
            'verification' => $verification,
        ]);

        if (! $verification || ! isset($verification['status']) || ! isset($verification['data'])) {
            Log::error('Chapa verification invalid response', [
                'tx_ref' => $txRef,
                'verification' => $verification,
            ]);

            return response()->json(['message' => 'Invalid response from Chapa API'], 400);
        }

        Log::info('Payment status check', [
            'top_level_status' => $verification['status'],
            'data_status' => $verification['data']['status'] ?? 'N/A',
        ]);

        // Check for successful payment (status could be 'success', 'successful', or contain 'success')
        $dataStatus = $verification['data']['status'] ?? '';
        $isSuccessful = ($verification['status'] === 'success' || $verification['status'] === 'successful')
            && (stripos($dataStatus, 'success') !== false || $dataStatus === 'successful' || $dataStatus === 'success');

        if ($isSuccessful) {
            $meta = $verification['data']['meta'] ?? [];
            $planId = $meta['plan_id'] ?? null;
            $userId = $meta['user_id'] ?? null;

            if (! $planId || ! $userId) {
                Log::error('Missing plan or user in meta', [
                    'meta' => $meta,
                    'tx_ref' => $txRef,
                ]);

                return response()->json(['message' => 'Missing plan or user info', 400]);
            }

            $plan = Plan::findOrFail($planId);

            $existingSubscription = Subscription::where('user_id', $userId)->first();

            if ($existingSubscription) {
                $existingSubscription->update([
                    'remaining_posts' => $existingSubscription->remaining_posts + $plan->job_posts_limit,
                    'direct_requests_remaining' => ($existingSubscription->direct_requests_remaining ?? 0) + $plan->direct_requests_limit,
                    'expires_at' => now()->addDays($plan->duration_days),
                    'status' => 'active',
                    'tx_ref' => $txRef,
                ]);
            } else {
                Subscription::create([
                    'user_id' => $userId,
                    'plan_id' => $planId,
                    'remaining_posts' => $plan->job_posts_limit,
                    'direct_requests_remaining' => $plan->direct_requests_limit,
                    'expires_at' => now()->addDays($plan->duration_days),
                    'status' => 'active',
                    'tx_ref' => $txRef,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment verified and subscription created successfully',
            ]);
        }

        Log::warning('Payment not successful', [
            'tx_ref' => $txRef,
            'status' => $verification['status'],
            'data_status' => $verification['data']['status'] ?? 'N/A',
        ]);

        return response()->json(['message' => 'Payment verification failed'], 400);
    }

    public function handlePaymentSuccess(Request $request)
    {
        $txRef = $request->query('tx_ref');

        Log::info('handlePaymentSuccess called', ['tx_ref' => $txRef, 'full_url' => $request->fullUrl()]);

        if (! $txRef) {
            return redirect('/#/?payment=error&message=No transaction reference');
        }

        $chapa = new Chapa;
        $verification = $chapa->verifyTransaction($txRef);

        Log::info('Chapa verification in handlePaymentSuccess', [
            'tx_ref' => $txRef,
            'verification' => $verification,
        ]);

        if (! $verification || ! isset($verification['status']) || ! isset($verification['data'])) {
            return redirect('/#/?payment=error&message=Invalid payment response');
        }

        $dataStatus = $verification['data']['status'] ?? '';
        $isSuccessful = ($verification['status'] === 'success' || $verification['status'] === 'successful')
            && (stripos($dataStatus, 'success') !== false || $dataStatus === 'successful' || $dataStatus === 'success');

        Log::info('Payment status check handlePaymentSuccess', [
            'top_status' => $verification['status'],
            'data_status' => $dataStatus,
            'isSuccessful' => $isSuccessful,
        ]);

        if ($isSuccessful) {
            $meta = $verification['data']['meta'] ?? [];
            $planId = $meta['plan_id'] ?? null;
            $userId = $meta['user_id'] ?? null;

            Log::info('Processing successful payment', [
                'tx_ref' => $txRef,
                'planId' => $planId,
                'userId' => $userId,
            ]);

            if ($planId && $userId) {
                $plan = Plan::findOrFail($planId);
                $existingSubscription = Subscription::where('user_id', $userId)->first();

                Log::info('Subscription check', [
                    'existing' => $existingSubscription ? 'yes' : 'no',
                    'plan' => $plan->name,
                ]);

                if ($existingSubscription) {
                    $existingSubscription->update([
                        'remaining_posts' => $existingSubscription->remaining_posts + $plan->job_posts_limit,
                        'direct_requests_remaining' => ($existingSubscription->direct_requests_remaining ?? 0) + $plan->direct_requests_limit,
                        'expires_at' => now()->addDays($plan->duration_days),
                        'status' => 'active',
                        'tx_ref' => $txRef,
                    ]);

                    Log::info('Subscription updated with tx_ref', ['tx_ref' => $txRef, 'new_remaining' => $existingSubscription->remaining_posts]);
                } else {
                    $subscription = Subscription::create([
                        'user_id' => $userId,
                        'plan_id' => $planId,
                        'remaining_posts' => $plan->job_posts_limit,
                        'direct_requests_remaining' => $plan->direct_requests_limit,
                        'expires_at' => now()->addDays($plan->duration_days),
                        'status' => 'active',
                        'tx_ref' => $txRef,
                    ]);

                    Log::info('Subscription created with tx_ref', ['tx_ref' => $txRef, 'id' => $subscription->id]);
                }

                return view('payment.success');
            }
        }

        return view('payment.failed');
    }
}
