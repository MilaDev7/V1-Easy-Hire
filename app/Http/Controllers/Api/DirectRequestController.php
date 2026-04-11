<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\DirectRequest;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DirectRequestController extends Controller
{
    private const REQUEST_EXPIRY_HOURS = 48;

    private function expirePendingRequestsForProfessional(int $professionalId): void
    {
        DirectRequest::where('professional_id', $professionalId)
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    private function expirePendingRequestsForClient(int $clientId): void
    {
        DirectRequest::where('client_id', $clientId)
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    private function formatRequest(DirectRequest $req): array
    {
        $secondsRemaining = null;
        if ($req->status === 'pending' && $req->expires_at) {
            $secondsRemaining = max(0, now()->diffInSeconds($req->expires_at, false));
        }

        return [
            'id' => $req->id,
            'title' => $req->title,
            'description' => $req->description,
            'budget' => $req->budget,
            'status' => $req->status,
            'created_at' => $req->created_at,
            'expires_at' => $req->expires_at,
            'seconds_remaining' => $secondsRemaining,
            'client' => $req->client ? [
                'id' => $req->client->id,
                'name' => $req->client->name,
            ] : null,
            'professional' => $req->professional ? [
                'id' => $req->professional->id,
                'name' => $req->professional->name,
            ] : null,
        ];
    }

    // Client sends direct request to professional
    public function sendRequest(Request $request, $proId)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'budget' => 'nullable|numeric',
        ]);

        $clientId = auth()->id();
        $this->expirePendingRequestsForClient($clientId);

        // Keep subscription validation, but do not deduct on send.
        $subscription = Subscription::where('user_id', $clientId)
            ->where('status', 'active')
            ->first();

        if (! $subscription) {
            return response()->json(['message' => 'No active subscription'], 403);
        }

        // Enforce expiry even when stale records are still marked as active.
        if ($subscription->expires_at && Carbon::parse($subscription->expires_at)->isPast()) {
            $subscription->status = 'expired';
            $subscription->save();

            return response()->json([
                'message' => 'Your subscription has expired. Please renew your plan.',
            ], 403);
        }

        // Enforce direct request limit at send-time as well.
        $remainingRequests = (int) ($subscription->direct_requests_remaining ?? 0);
        if ($remainingRequests <= 0) {
            return response()->json([
                'message' => 'Direct requests limit reached. Please upgrade your plan.',
            ], 403);
        }

        // Check if professional exists
        $professional = \App\Models\Professional::where('id', $proId)->first();
        if (! $professional) {
            return response()->json(['message' => 'Professional not found (id: '.$proId.')'], 404);
        }
        if ($professional->status !== 'approved') {
            return response()->json(['message' => 'Professional not approved. Status: '.$professional->status], 404);
        }

        $proUserId = $professional->user_id;

        // Check if already sent
        $exists = DirectRequest::where('client_id', $clientId)
            ->where('professional_id', $proUserId)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Request already sent'], 400);
        }

        // Create request; direct request credits are deducted only when accepted.
        $directRequest = DirectRequest::create([
            'client_id' => $clientId,
            'professional_id' => $proUserId,
            'title' => $request->title,
            'description' => $request->description,
            'budget' => $request->budget,
            'status' => 'pending',
            'expires_at' => now()->addHours(self::REQUEST_EXPIRY_HOURS),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request sent successfully',
            'request' => $this->formatRequest($directRequest->load(['client:id,name', 'professional:id,name'])),
        ], 201);
    }

    // Professional views incoming requests
    public function myRequests()
    {
        $userId = auth()->id();
        $this->expirePendingRequestsForProfessional($userId);

        $requests = DirectRequest::where('professional_id', $userId)
            ->with(['client:id,name', 'professional:id,name'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (DirectRequest $req) => $this->formatRequest($req));

        return response()->json($requests);
    }

    // Client views own requests grouped by status
    public function clientRequests()
    {
        $clientId = auth()->id();
        $this->expirePendingRequestsForClient($clientId);

        $requests = DirectRequest::where('client_id', $clientId)
            ->with(['professional:id,name', 'client:id,name'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (DirectRequest $req) => $this->formatRequest($req));

        $grouped = [
            'pending' => [],
            'accepted' => [],
            'rejected' => [],
            'expired' => [],
        ];

        foreach ($requests as $request) {
            $status = $request['status'];
            if (array_key_exists($status, $grouped)) {
                $grouped[$status][] = $request;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $grouped,
            'counts' => [
                'pending' => count($grouped['pending']),
                'accepted' => count($grouped['accepted']),
                'rejected' => count($grouped['rejected']),
                'expired' => count($grouped['expired']),
            ],
        ]);
    }

    // Client cancels pending request (moves to rejected)
    public function cancelClientRequest($id)
    {
        $clientId = auth()->id();
        $this->expirePendingRequestsForClient($clientId);

        $directRequest = DirectRequest::where('id', $id)
            ->where('client_id', $clientId)
            ->where('status', 'pending')
            ->first();

        if (! $directRequest) {
            return response()->json(['message' => 'Pending request not found'], 404);
        }

        $directRequest->status = 'rejected';
        $directRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Request cancelled successfully',
        ]);
    }

    // Client resends an expired request
    public function resendClientRequest($id)
    {
        $clientId = auth()->id();
        $this->expirePendingRequestsForClient($clientId);

        $expiredRequest = DirectRequest::where('id', $id)
            ->where('client_id', $clientId)
            ->where('status', 'expired')
            ->first();

        if (! $expiredRequest) {
            return response()->json(['message' => 'Expired request not found'], 404);
        }

        $hasPending = DirectRequest::where('client_id', $clientId)
            ->where('professional_id', $expiredRequest->professional_id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return response()->json(['message' => 'You already have a pending request for this professional'], 400);
        }

        $newRequest = DirectRequest::create([
            'client_id' => $clientId,
            'professional_id' => $expiredRequest->professional_id,
            'title' => $expiredRequest->title,
            'description' => $expiredRequest->description,
            'budget' => $expiredRequest->budget,
            'status' => 'pending',
            'expires_at' => now()->addHours(self::REQUEST_EXPIRY_HOURS),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request resent successfully',
            'request' => $this->formatRequest($newRequest->load(['client:id,name', 'professional:id,name'])),
        ], 201);
    }

    // Professional accepts request - creates contract
    public function acceptRequest($id)
    {
        $userId = auth()->id();
        $this->expirePendingRequestsForProfessional($userId);

        $directRequest = DirectRequest::where('id', $id)
            ->where('professional_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (! $directRequest) {
            return response()->json(['message' => 'Request not found or already processed'], 404);
        }

        // Check if professional has 3 active contracts
        $activeContracts = Contract::where('professional_id', $userId)
            ->whereIn('status', ['active', 'pending_completion'])
            ->count();

        if ($activeContracts >= 3) {
            return response()->json(['message' => 'You have 3 active contracts. Complete them before accepting more.'], 400);
        }

        // Deduct direct-request credit only when request is accepted.
        $clientSubscription = Subscription::where('user_id', $directRequest->client_id)
            ->where('status', 'active')
            ->first();

        if (! $clientSubscription) {
            return response()->json(['message' => 'Client has no active subscription.'], 400);
        }

        $remaining = (int) ($clientSubscription->direct_requests_remaining ?? 0);
        if ($remaining < 1) {
            return response()->json(['message' => 'Client has no direct request credits remaining.'], 400);
        }

        try {
            $contractId = DB::transaction(function () use ($directRequest, $clientSubscription, $userId) {
                $directRequest->status = 'accepted';
                $directRequest->save();
                $clientSubscription->decrement('direct_requests_remaining');

                \Log::info('Creating contract for direct request', [
                    'direct_request_id' => $directRequest->id,
                    'client_id' => $directRequest->client_id,
                    'professional_id' => $userId,
                    'budget' => $directRequest->budget,
                ]);

                // Create contract with direct request reference
                $client = User::find($directRequest->client_id);
                $professional = User::find($userId);

                $contract = Contract::create([
                    'direct_request_id' => $directRequest->id,
                    'client_id' => $directRequest->client_id,
                    'client_phone' => $client?->phone,
                    'professional_id' => $userId,
                    'professional_phone' => $professional?->phone,
                    'agreed_price' => $directRequest->budget,
                    'status' => 'active',
                ]);

                return $contract->id;
            });

            \Log::info('Contract created successfully', ['contract_id' => $contractId]);

            return response()->json([
                'success' => true,
                'message' => 'Request accepted! Contract created.',
                'contract_id' => $contractId,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create contract', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create contract: '.$e->getMessage(),
            ], 500);
        }
    }

    // Professional rejects request
    public function rejectRequest($id)
    {
        $userId = auth()->id();
        $this->expirePendingRequestsForProfessional($userId);

        $directRequest = DirectRequest::where('id', $id)
            ->where('professional_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (! $directRequest) {
            return response()->json(['message' => 'Request not found or already processed'], 404);
        }

        // Update status
        $directRequest->status = 'rejected';
        $directRequest->save();

        return response()->json([
            'message' => 'Request rejected',
        ]);
    }
}
