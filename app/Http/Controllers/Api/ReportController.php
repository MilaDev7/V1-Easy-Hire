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
            'reason' => 'required|string',
            'message' => 'nullable|string|max:1000',
        ]);

        $contract = Contract::findOrFail($contractId);

        $userId = auth()->id();

        // ✅ Must be part of contract
        if ($contract->client_id !== $userId && $contract->professional_id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Professional can report client only when contract is active or completed.
        if ($contract->professional_id === $userId && ! in_array($contract->status, ['active', 'completed'], true)) {
            return response()->json(['message' => 'You can report client only for active or completed contracts'], 400);
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

        $reason = trim((string) $request->reason);
        $message = trim((string) $request->message);
        $reportReason = $message !== ''
            ? $reason.' | '.$message
            : $reason;

        $report = Report::create([
            'contract_id' => $contractId,
            'reporter_id' => $userId,
            'reported_id' => $reportedId,
            'reason' => $reportReason,
        ]);

        return response()->json([
            'message' => 'Report submitted',
            'report' => $report
        ]);
    }
}
