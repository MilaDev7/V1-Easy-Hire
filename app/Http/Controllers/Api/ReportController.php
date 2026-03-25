<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Contract;

class ReportController extends Controller
{
    public function store(Request $request, $contractId)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        $contract = Contract::findOrFail($contractId);

        $userId = auth()->id();

        // ✅ Must be part of contract
        if ($contract->client_id !== $userId && $contract->professional_id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // ✅ Determine who is being reported
        $reportedId = ($contract->client_id === $userId)
            ? $contract->professional_id
            : $contract->client_id;

        // ✅ Prevent duplicate
        $exists = Report::where('contract_id', $contractId)
            ->where('reporter_id', $userId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Already reported'], 400);
        }

        $report = Report::create([
            'contract_id' => $contractId,
            'reporter_id' => $userId,
            'reported_id' => $reportedId,
            'reason' => $request->reason
        ]);

        return response()->json([
            'message' => 'Report submitted',
            'report' => $report
        ]);
    }
}
