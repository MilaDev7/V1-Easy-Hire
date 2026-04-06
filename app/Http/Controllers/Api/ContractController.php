<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\JobPost;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    // PROFESSIONAL MARKS COMPLETED
    public function markCompleted($id)
    {
        $contract = \App\Models\Contract::find($id);

        if (! $contract) {
            return response()->json(['message' => 'Contract not found'], 404);
        }

        // 🔒 Only assigned professional
        if ($contract->professional_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // ❌ Already completed
        if ($contract->status !== 'active') {
            return response()->json(['message' => 'Already completed or cancelled'], 400);
        }

        // ✅ Mark completed (waiting client confirmation)
        $contract->status = 'completed';
        $contract->save();

        return response()->json([
            'message' => 'Work marked as completed, waiting for client confirmation',
        ]);
    }

    // CLIENT CONFIRMS
    public function confirm($id)
    {
        $contract = Contract::find($id);

        if (! $contract) {
            return response()->json(['message' => 'Contract not found'], 404);
        }

        // 🔒 Only client
        if ($contract->client_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($contract->status === 'cancelled') {
            return response()->json(['message' => 'Cancelled contract cannot be confirmed'], 400);
        }

        $contract->status = 'completed';
        $contract->save();

        if ($contract->job) {
            $contract->job->status = 'completed';
            $contract->job->save();
        }

        return response()->json([
            'message' => 'Job completed successfully',
        ]);
    }

    // client cancel contract
    public function cancel($id)
    {
        $contract = \App\Models\Contract::findOrFail($id);

        // ✅ Only client can cancel
        if ($contract->client_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($contract->status === 'completed') {
            return response()->json([
                'message' => 'Cannot cancel completed contract',
            ], 400);
        }

        // ❌ Already cancelled
        if ($contract->status === 'cancelled') {
            return response()->json([
                'message' => 'Contract already cancelled',
            ], 400);
        }

        $contract->status = 'cancelled';
        $contract->save();

        $job = JobPost::findOrFail($contract->job_id);
        $job->status = 'cancelled';
        $job->save();

        return response()->json([
            'message' => 'Contract cancelled successfully',
        ]);

    }

    public function myContracts(Request $request)
    {
        $userId = auth()->id();

        $query = \App\Models\Contract::where('professional_id', $userId);

        // ✅ Optional filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $contracts = $query->with(['job', 'client', 'directRequest'])->get();

        return response()->json([
            'contracts' => $contracts,
        ]);
    }
}
