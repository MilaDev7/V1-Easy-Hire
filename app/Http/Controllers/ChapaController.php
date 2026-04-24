<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\ApplyCreditService;
use App\Services\Chapa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChapaController extends Controller
{
    public function __construct(private ApplyCreditService $applyCreditService) {}

    /**
     * Apply subscription credit exactly once per tx_ref.
     */
    private function applyPaymentIfNew(string $txRef, int $planId, int $userId): array
    {
        return DB::transaction(function () use ($txRef, $planId, $userId) {
            $inserted = DB::table('processed_payment_transactions')->insertOrIgnore([
                'tx_ref' => $txRef,
                'user_id' => $userId,
                'plan_id' => $planId,
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Already processed in a previous callback/return-page attempt.
            if ($inserted === 0) {
                return ['already_processed' => true];
            }

            $plan = Plan::findOrFail($planId);
            if ($plan->plan_scope === 'professional_monthly') {
                $this->applyCreditService->activateMonthlyPlan($userId, $plan);

                return ['already_processed' => false];
            }

            if ($plan->plan_scope === 'professional_extra') {
                $this->applyCreditService->addExtraApplies($userId, (int) $plan->extra_apply_quantity);

                return ['already_processed' => false];
            }

            $subscription = Subscription::where('user_id', $userId)->lockForUpdate()->first();

            if ($subscription) {
                $subscription->update([
                    'remaining_posts' => $subscription->remaining_posts + $plan->job_posts_limit,
                    'direct_requests_remaining' => ($subscription->direct_requests_remaining ?? 0) + $plan->direct_requests_limit,
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

            return ['already_processed' => false];
        });
    }

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

        // Use user name as tx_ref prefix (sanitized for gateway safety).
        $namePrefix = strtolower(trim((string) $user->name));
        $namePrefix = preg_replace('/[^a-z0-9]+/', '_', $namePrefix);
        $namePrefix = trim((string) $namePrefix, '_');
        if ($namePrefix === '') {
            $namePrefix = 'user_'.$user->id;
        }
        $txRef = Chapa::generateReference($namePrefix);
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
        // Some gateway callbacks use trx_ref instead of tx_ref.
        $txRef = $request->query('tx_ref') ?? $request->query('trx_ref');

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

                    return response()->json(['message' => 'Missing plan or user info'], 400);
                }

            $result = $this->applyPaymentIfNew($txRef, (int) $planId, (int) $userId);

            return response()->json([
                'success' => true,
                'message' => $result['already_processed']
                    ? 'Payment already processed'
                    : 'Payment verified and subscription created successfully',
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
        // Some return payloads use trx_ref instead of tx_ref.
        $txRef = $request->query('tx_ref') ?? $request->query('trx_ref');

        Log::info('handlePaymentSuccess called', ['tx_ref' => $txRef, 'full_url' => $request->fullUrl()]);

        if (! $txRef) {
            return view('payment.failed');
        }

        $chapa = new Chapa;
        $verification = $chapa->verifyTransaction($txRef);

        Log::info('Chapa verification in handlePaymentSuccess', [
            'tx_ref' => $txRef,
            'verification' => $verification,
        ]);

        if (! $verification || ! isset($verification['status']) || ! isset($verification['data'])) {
            return view('payment.failed');
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
                $result = $this->applyPaymentIfNew($txRef, (int) $planId, (int) $userId);
                Log::info('Subscription processing result', [
                    'tx_ref' => $txRef,
                    'already_processed' => $result['already_processed'],
                ]);

                return view('payment.success', [
                    'receipt' => [
                        'tx_ref' => $txRef,
                        'reference' => $verification['data']['reference'] ?? null,
                        'amount' => $verification['data']['amount'] ?? null,
                        'currency' => $verification['data']['currency'] ?? 'ETB',
                        'email' => $verification['data']['email'] ?? null,
                        'first_name' => $verification['data']['first_name'] ?? null,
                        'status' => $verification['data']['status'] ?? 'success',
                        'paid_at' => $verification['data']['updated_at'] ?? $verification['data']['created_at'] ?? now()->toDateTimeString(),
                        'plan_id' => $planId,
                    ],
                ]);
            }
        }

        return view('payment.failed');
    }
}
