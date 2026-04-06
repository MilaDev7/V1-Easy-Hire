<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\DirectRequest;
use App\Models\Subscription;
use Illuminate\Http\Request;

class DirectRequestController extends Controller
{
    // Client sends direct request to professional
    public function sendRequest(Request $request, $proId)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'budget' => 'nullable|numeric',
        ]);

        $clientId = auth()->id();

        // Check subscription has direct requests remaining
        $subscription = Subscription::where('user_id', $clientId)
            ->where('status', 'active')
            ->first();

        if (! $subscription) {
            return response()->json(['message' => 'No active subscription'], 403);
        }

        $remaining = $subscription->direct_requests_remaining ?? 0;
        if ($remaining < 1) {
            return response()->json(['message' => 'No direct requests remaining. Upgrade your plan. Current: '.$remaining], 403);
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

        // Create request and decrement direct requests
        $directRequest = DirectRequest::create([
            'client_id' => $clientId,
            'professional_id' => $proUserId,
            'title' => $request->title,
            'description' => $request->description,
            'budget' => $request->budget,
            'status' => 'pending',
        ]);

        // Decrement remaining direct requests when client sends request
        if ($subscription && $subscription->direct_requests_remaining > 0) {
            $subscription->decrement('direct_requests_remaining');
        }

        return response()->json([
            'success' => true,
            'message' => 'Request sent successfully',
            'request' => $directRequest,
        ], 201);
    }

    // Professional views incoming requests
    public function myRequests()
    {
        $userId = auth()->id();

        $requests = DirectRequest::where('professional_id', $userId)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'title' => $req->title,
                    'description' => $req->description,
                    'budget' => $req->budget,
                    'status' => $req->status,
                    'created_at' => $req->created_at,
                    'client' => $req->client ? [
                        'id' => $req->client->id,
                        'name' => $req->client->name,
                    ] : null,
                ];
            });

        return response()->json($requests);
    }

    // Professional accepts request - creates contract
    public function acceptRequest($id)
    {
        $userId = auth()->id();

        $directRequest = DirectRequest::where('id', $id)
            ->where('professional_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (! $directRequest) {
            return response()->json(['message' => 'Request not found or already processed'], 404);
        }

        // Check if professional has 3 active contracts
        $activeContracts = Contract::where('professional_id', $userId)
            ->where('status', 'active')
            ->count();

        if ($activeContracts >= 3) {
            return response()->json(['message' => 'You have 3 active contracts. Complete them before accepting more.'], 400);
        }

        $directRequest->status = 'accepted';
        $directRequest->save();

        \Log::info('Creating contract for direct request', [
            'direct_request_id' => $directRequest->id,
            'client_id' => $directRequest->client_id,
            'professional_id' => $userId,
            'budget' => $directRequest->budget,
        ]);

        try {
            // Create contract with direct request reference
            $contract = Contract::create([
                'direct_request_id' => $directRequest->id,
                'client_id' => $directRequest->client_id,
                'professional_id' => $userId,
                'agreed_price' => $directRequest->budget,
                'status' => 'active',
            ]);

            \Log::info('Contract created successfully', ['contract_id' => $contract->id]);

            return response()->json([
                'success' => true,
                'message' => 'Request accepted! Contract created.',
                'contract_id' => $contract->id,
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
