<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\JobPost;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    // PROFESSIONAL MARKS COMPLETED
    public function markCompleted($id)
    {
        Contract::autoCompleteExpiredPendingCompletions();

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

        // ✅ Mark pending completion (waiting client confirmation)
        $contract->status = 'pending_completion';
        $contract->save();

        return response()->json([
            'message' => 'Work marked as pending completion, waiting for client confirmation',
        ]);
    }

    // CLIENT CONFIRMS
    public function confirm($id)
    {
        Contract::autoCompleteExpiredPendingCompletions();

        $contract = Contract::find($id);

        if (! $contract) {
            return response()->json(['message' => 'Contract not found'], 404);
        }

        // 🔒 Only client
        if ($contract->client_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($contract->status !== 'pending_completion') {
            return response()->json(['message' => 'Only pending completion contracts can be confirmed'], 400);
        }

        $contract->status = 'completed';
        $contract->save();

        if ($contract->job) {
            $contract->job->status = 'completed';
            $contract->job->save();
        }

        $title = $contract->job?->title ?? $contract->directRequest?->title ?? 'Contract Work';
        $this->notificationService->send(
            (int) $contract->professional_id,
            'completion_confirmed',
            'Completion confirmed',
            'Client confirmed completion: '.$title,
            '/pro/dashboard?view=my-contracts',
            ['contract_id' => $contract->id]
        );

        return response()->json([
            'message' => 'Job completed successfully',
        ]);
    }

    // CLIENT REJECTS COMPLETION AND RETURNS CONTRACT TO ACTIVE
    public function reject($id)
    {
        Contract::autoCompleteExpiredPendingCompletions();

        $contract = Contract::find($id);

        if (! $contract) {
            return response()->json(['message' => 'Contract not found'], 404);
        }

        if ($contract->client_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($contract->status !== 'pending_completion') {
            return response()->json(['message' => 'Only pending completion contracts can be rejected'], 400);
        }

        $contract->status = 'active';
        $contract->save();

        $title = $contract->job?->title ?? $contract->directRequest?->title ?? 'Contract Work';
        $this->notificationService->send(
            (int) $contract->professional_id,
            'completion_rejected',
            'Completion rejected',
            'Client rejected completion request: '.$title,
            '/pro/dashboard?view=my-contracts',
            ['contract_id' => $contract->id]
        );

        return response()->json([
            'message' => 'Completion rejected. Contract set back to active',
        ]);
    }

    // client cancel contract
    public function cancel($id)
    {
        Contract::autoCompleteExpiredPendingCompletions();

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

        if ($contract->job_id) {
            $job = JobPost::find($contract->job_id);
            if ($job) {
                $job->status = 'cancelled';
                $job->save();
            }
        }

        return response()->json([
            'message' => 'Contract cancelled successfully',
        ]);

    }

    public function myContracts(Request $request)
    {
        Contract::autoCompleteExpiredPendingCompletions();

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
