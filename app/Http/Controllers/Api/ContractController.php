<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    //PROFESSIONAL MARKS COMPLETED
    public function markCompleted($id)
    {
          $contract = \App\Models\Contract::find($id);

        if (!$contract) {
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
            'message' => 'Work marked as completed, waiting for client confirmation'
        ]);
    }

    //CLIENT CONFIRMS
        public function confirm($id)
    {
        $contract = Contract::find($id);

        if (!$contract) {
            return response()->json(['message' => 'Contract not found'], 404);
        }

        // 🔒 Only client
        if ($contract->client_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($contract->status !== 'completed') {
            return response()->json(['message' => 'Work not completed yet'], 400);
        }

        // ✅ Final confirmation
        $contract->status = 'completed';
        $contract->save();

        // 🔥 ALSO update job
        $contract->job->status = 'completed';
        $contract->job->save();

        return response()->json([
            'message' => 'Job completed successfully'
        ]);
    }

};