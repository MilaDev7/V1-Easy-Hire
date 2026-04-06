<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ProfessionalStatusMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    // All Professionals
    public function allProfessionals()
    {
        $pros = \App\Models\Professional::with('user')
            ->latest()
            ->get()
            ->map(function ($pro) {
                return [
                    'id' => $pro->id,
                    'name' => $pro->user->name ?? '',
                    'email' => $pro->user->email ?? '',
                    'role' => 'professional',
                    'skill' => $pro->skill,
                    'location' => $pro->location,
                    'status' => $pro->status,
                    'is_suspended' => $pro->user->is_suspended ?? false,
                ];
            });

        return response()->json($pros);
    }

    // Pending Professionals
    public function pendingProfessionals()
    {
        $pros = \App\Models\Professional::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(function ($pro) {
                return [
                    'id' => $pro->id,
                    'name' => $pro->user->name ?? '',
                    'email' => $pro->user->email ?? '',
                    'skill' => $pro->skill,
                    'location' => $pro->location,
                    'cv' => $pro->cv,
                    'certificate' => $pro->certificate,
                    'id_card' => $pro->id_card,
                    'profile_photo' => $pro->profile_photo,
                    'status' => $pro->status,
                ];
            });

        return response()->json($pros);
    }

    // approveProfessional

    public function approveProfessional($id)
    {
        $professional = \App\Models\Professional::with('user')->findOrFail($id);

        $professional->status = 'approved';
        $professional->save();

        if ($professional->user) {
            Mail::to($professional->user->email)->send(
                new ProfessionalStatusMail('Your account has been approved. You can now start applying for jobs.')
            );
        }

        return response()->json([
            'message' => 'Professional approved successfully',
            'professional' => $professional,
        ]);
    }

    // rejectProfessional

    public function rejectProfessional($id)
    {

        $professional = \App\Models\Professional::findOrFail($id);

        if (! $professional) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $professional->status = 'rejected';
        $professional->save();
        // Mail
        if ($professional->user) {
            Mail::to($professional->user->email)->send(
                new ProfessionalStatusMail('Your account has been rejected. Please contact support.')
            );
        }

        return response()->json([
            'message' => 'Professional rejected',
        ]);

    }

    // suspendUser

    public function suspendUser($id)
    {
        $user = User::findOrFail($id);

        $user->is_suspended = true;
        $user->save();

        $user->tokens()->delete();

        try {
            Mail::to($user->email)->send(
                new ProfessionalStatusMail('Your account has been suspended due to a violation of our terms. All active sessions have been closed.')
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send suspension email: '.$e->getMessage());
        }

        return response()->json([
            'message' => 'User suspended successfully',
        ]);
    }

    public function getSuspendedUsers()
    {
        $users = User::withTrashed()->where('is_suspended', true)->get();

        return response()->json($users);
    }

    // unsuspendUser

    public function unsuspendUser($id)
    {
        // 1. Find the user
        $user = User::findOrFail($id);

        $user->is_suspended = false;
        $user->save();

        // 2. Send Email (Make sure this is BEFORE the return)
        try {
            Mail::to($user->email)->send(
                new ProfessionalStatusMail('Great news! Your account has been restored. You can now log back in and use the system.')
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send unsuspension email: '.$e->getMessage());
        }

        // 3. RETURN MUST BE LAST
        return response()->json([
            'message' => 'User unsuspended successfully',
        ]);
    }

    // reports
    public function reports(Request $request)
    {
        $query = \App\Models\Report::with([
            'contract',
            'reporter:id,name,email',
            'reported:id,name,email',
        ]);

        if ($request->status === 'resolved') {
            $query->where('status', 'resolved');
        }

        $reports = $query->latest()->get()->map(function ($report) {
            return [
                'id' => $report->id,
                'reason' => $report->reason,
                'status' => $report->status,
                'action_taken' => $report->action_taken,
                'resolved_at' => $report->resolved_at,
                'created_at' => $report->created_at,
                'reporter' => $report->reporter ? ['id' => $report->reporter->id, 'name' => $report->reporter->name, 'email' => $report->reporter->email] : null,
                'reported' => $report->reported ? ['id' => $report->reported->id, 'name' => $report->reported->name, 'email' => $report->reported->email] : null,
                'contract' => $report->contract ? [
                    'id' => $report->contract->id,
                    'status' => $report->contract->status,
                    'agreed_price' => $report->contract->agreed_price,
                    'client' => $report->contract->client ? ['id' => $report->contract->client->id, 'name' => $report->contract->client->name] : null,
                    'professional' => $report->contract->professional ? ['id' => $report->contract->professional->id, 'name' => $report->contract->professional->name] : null,
                    'job' => $report->contract->job ? ['id' => $report->contract->job->id, 'title' => $report->contract->job->title, 'description' => $report->contract->job->description] : null,
                ] : null,
            ];
        });

        return response()->json($reports);
    }

    // resolveReport

    public function resolveReport(Request $request, $id)
    {
        $report = \App\Models\Report::with('contract')->findOrFail($id);

        // ❌ prevent double resolve
        if ($report->status === 'resolved') {
            return response()->json(['message' => 'Report already resolved'], 400);
        }

        $actionTaken = 'no_action';

        // 🔴 Suspend User
        if ($request->action === 'suspend_user') {
            $user = \App\Models\User::find($report->reported_id);

            if ($user) {
                $user->is_suspended = true;
                $user->tokens()->delete();
                $user->save();

                $actionTaken = 'suspend_user';
            }
        }

        // 🔴 Cancel Contract
        if ($request->action === 'cancel_contract' && $report->contract) {
            $contract = \App\Models\Contract::find($report->contract->id);

            if ($contract && $contract->status !== 'completed') {
                $contract->status = 'cancelled';
                $contract->save();

                $actionTaken = 'cancel_contract';
            }
        }

        // ✅ Save resolution
        $report->status = 'resolved';
        $report->action_taken = $actionTaken;
        $report->resolved_at = now();
        $report->save();

        return response()->json([
            'message' => 'Report resolved successfully',
            'action_taken' => $actionTaken,
        ]);
    }

    // user view
    public function users()
    {
        $users = \App\Models\User::withTrashed()->select('id', 'name', 'email', 'role', 'is_suspended', 'deleted_at', 'created_at')
            ->latest()
            ->get();

        return response()->json($users);
    }

    // View all jobs

    public function jobs()
    {
        $jobs = \App\Models\JobPost::with(['client', 'applications'])
            ->latest()
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'description' => $job->description,
                    'skill' => $job->skill,
                    'location' => $job->location,
                    'budget' => $job->budget,
                    'status' => $job->status,
                    'client' => $job->client ? [
                        'id' => $job->client->id,
                        'name' => $job->client->name,
                        'email' => $job->client->email,
                    ] : null,
                    'applications_count' => $job->applications->count(),
                    'created_at' => $job->created_at,
                ];
            });

        return response()->json($jobs);
    }

    // Cancel Job
    public function cancelJob($id)
    {
        $job = \App\Models\JobPost::findOrFail($id);

        $applicationCount = \App\Models\Application::where('job_id', $job->id)->count();

        if ($job->status !== 'open') {
            return response()->json([
                'message' => 'Only open jobs can be cancelled',
            ], 400);
        }

        // Refund if no applications
        if ($applicationCount === 0) {
            $subscription = \App\Models\Subscription::where('user_id', $job->client_id)
                ->where('status', 'active')
                ->first();

            if ($subscription) {
                $subscription->increment('remaining_posts');
            }
        }

        $job->status = 'cancelled';
        $job->save();

        return response()->json([
            'message' => $applicationCount === 0
                ? 'Job cancelled successfully. 1 post refunded.'
                : 'Job cancelled successfully.',
        ]);
    }

    // View contract
    public function contracts()
    {
        $contracts = \App\Models\Contract::with(['client', 'professional', 'job'])->latest()->get()->map(function ($contract) {
            return [
                'id' => $contract->id,
                'status' => $contract->status,
                'budget' => $contract->agreed_price ?? $contract->job?->budget,
                'created_at' => $contract->created_at,
                'client' => $contract->client ? ['id' => $contract->client->id, 'name' => $contract->client->name, 'email' => $contract->client->email] : null,
                'professional' => $contract->professional ? ['id' => $contract->professional->id, 'name' => $contract->professional->name, 'email' => $contract->professional->email] : null,
                'job' => $contract->job ? ['id' => $contract->job->id, 'title' => $contract->job->title, 'description' => $contract->job->description] : null,
            ];
        });

        return response()->json($contracts);
    }

    // forceCancelContract

    public function forceCancelContract($id)
    {
        $contract = \App\Models\Contract::findOrFail($id);

        if ($contract->status === 'completed') {
            return response()->json([
                'message' => 'Cannot cancel completed contract',
            ], 400);
        }

        if ($contract->status === 'cancelled') {
            return response()->json([
                'message' => 'Contract already cancelled',
            ], 400);
        }

        $contract->status = 'cancelled';
        $contract->save();

        return response()->json([
            'message' => 'Contract cancelled by admin',
        ]);
    }

    // Plan Management (Admin CRUD)

    // CreatePlan
    public function createPlan(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'job_posts_limit' => 'required|integer',
            'duration_days' => 'required|integer|min=1',
        ]);

        $plan = \App\Models\Plan::create($request->all());

        return response()->json([
            'message' => 'Plan created',
            'plan' => $plan,
        ]);
    }

    // View plan
    public function plans()
    {
        $plans = \App\Models\Plan::all();

        return response()->json($plans);
    }

    // Updata plan
    public function updatePlan(Request $request, $id)
    {
        $plan = \App\Models\Plan::findOrFail($id);

        $plan->update($request->all());

        return response()->json([
            'message' => 'Plan updated',
            'plan' => $plan,
        ]);
    }

    // Delete plan

    public function deletePlan($id)
    {
        $plan = \App\Models\Plan::with('subscriptions')->findOrFail($id);

        if ($plan->subscriptions()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete plan. Users are subscribed to this plan.',
            ], 400);
        }

        $plan->delete();

        return response()->json([
            'message' => 'Plan deleted',
        ]);
    }

    // VIEW DELETED USERS

    public function deletedUsers()
    {
        $users = User::onlyTrashed()->select('id', 'name', 'email', 'role', 'is_suspended', 'deleted_at', 'created_at')->get();

        return response()->json($users);
    }

    // RESTORE USER

    public function restoreUser($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if (! $user->trashed()) {
            return response()->json([
                'message' => 'User is not deleted',
            ], 400);
        }

        $user->restore();

        return response()->json([
            'message' => 'User restored successfully',
        ]);
    }

    public function stats()
    {
        return response()->json([
            'pending_professionals' => \App\Models\Professional::where('status', 'pending')->count(),
            'active_contracts' => \App\Models\Contract::where('status', 'active')->count(),
            'total_users' => User::count(),
            'open_reports' => \App\Models\Report::where('status', 'pending')->count(),
        ]);
    }
}
