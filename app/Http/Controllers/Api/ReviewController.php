<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Contract;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, $contractId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $contract = Contract::find($contractId);

        if (!$contract) {
            return response()->json(['message' => 'Contract not found'], 404);
        }

        // ❌ Only after completion
        if ($contract->status !== 'completed') {
            return response()->json(['message' => 'Job not completed yet'], 400);
        }

        $userId = auth()->id();

        // 🔥 Determine who is reviewed
        if ($userId == $contract->client_id) {
            $reviewedId = $contract->professional_id;
        } elseif ($userId == $contract->professional_id) {
            $reviewedId = $contract->client_id;
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // ❌ prevent duplicate
        $exists = Review::where('contract_id', $contractId)
            ->where('reviewer_id', $userId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You already reviewed'], 400);
        }

        Review::create([
            'contract_id' => $contractId,
            'reviewer_id' => $userId,
            'reviewed_id' => $reviewedId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully'
        ]);
    }
}