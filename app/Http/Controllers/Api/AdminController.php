<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ProfessionalStatusMail;
use App\Models\Message;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

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
                'action_taken' => $report->action_taken ?: 'none',
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

        $request->validate([
            'action' => 'nullable|in:none,warning,suspend_user,cancel_contract',
        ]);

        // ❌ prevent double resolve
        if ($report->status === 'resolved') {
            return response()->json(['message' => 'Report already resolved'], 400);
        }

        $actionTaken = $request->input('action', 'none');

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

        // 🟡 Warning only (audit trail)
        if ($request->action === 'warning') {
            $actionTaken = 'warning';
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

    public function contactUser(Request $request, $id)
    {
        $targetUser = User::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'subject' => 'nullable|string|max:160',
            'message' => 'required|string|max:2000',
            'send_email' => 'nullable|boolean',
        ]);

        $subject = trim((string) ($validated['subject'] ?? ''));
        $messageBody = trim((string) $validated['message']);
        $sendEmail = (bool) ($validated['send_email'] ?? false);

        $message = Message::create([
            'sender_id' => (int) $request->user()->id,
            'receiver_id' => (int) $targetUser->id,
            'subject' => $subject !== '' ? $subject : null,
            'message' => $messageBody,
            'email_requested' => $sendEmail,
            'email_sent_at' => null,
        ]);

        app(NotificationService::class)->send(
            (int) $targetUser->id,
            'admin_message',
            $subject !== '' ? $subject : 'Message from Admin',
            $messageBody
        );

        if ($sendEmail && ! empty($targetUser->email)) {
            try {
                Mail::raw(
                    ($subject !== '' ? ($subject."\n\n") : '').$messageBody,
                    function ($mail) use ($targetUser, $subject) {
                        $mail->to($targetUser->email)
                            ->subject($subject !== '' ? $subject : 'EasyHire admin message');
                    }
                );
                $message->email_sent_at = now();
                $message->save();
            } catch (\Throwable $exception) {
                \Log::error('Failed to send admin message email: '.$exception->getMessage());
            }
        }

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message,
        ]);
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
        \App\Models\Contract::autoCompleteExpiredPendingCompletions();

        $contracts = \App\Models\Contract::with(['client', 'professional', 'job', 'directRequest'])->latest()->get()->map(function ($contract) {
            return [
                'id' => $contract->id,
                'status' => $contract->status,
                'budget' => $contract->agreed_price ?? $contract->job?->budget,
                'created_at' => $contract->created_at,
                'client' => $contract->client ? ['id' => $contract->client->id, 'name' => $contract->client->name, 'email' => $contract->client->email, 'phone' => $contract->client_phone ?? $contract->client->phone ?? 'N/A'] : null,
                'professional' => $contract->professional ? ['id' => $contract->professional->id, 'name' => $contract->professional->name, 'email' => $contract->professional->email, 'phone' => $contract->professional_phone ?? $contract->professional->phone ?? 'N/A'] : null,
                'job' => $contract->job ? ['id' => $contract->job->id, 'title' => $contract->job->title, 'description' => $contract->job->description] : null,
                'direct_request' => $contract->directRequest ? ['id' => $contract->directRequest->id, 'title' => $contract->directRequest->title, 'description' => $contract->directRequest->description] : null,
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
            'plan_scope' => 'nullable|in:client,professional_monthly,professional_extra',
            'job_posts_limit' => 'nullable|integer|min:0',
            'direct_requests_limit' => 'nullable|integer|min:0',
            'duration_days' => 'nullable|integer|min:1',
            'apply_limit_monthly' => 'nullable|integer|min:0',
            'extra_apply_quantity' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $scope = $data['plan_scope'] ?? 'client';
        $data['plan_scope'] = $scope;

        if ($scope === 'professional_monthly') {
            $data['job_posts_limit'] = 0;
            $data['direct_requests_limit'] = 0;
            $data['apply_limit_monthly'] = max((int) ($data['apply_limit_monthly'] ?? 0), 0);
            $data['extra_apply_quantity'] = 0;
            $data['duration_days'] = max((int) ($data['duration_days'] ?? 30), 1);
        } elseif ($scope === 'professional_extra') {
            $data['job_posts_limit'] = 0;
            $data['direct_requests_limit'] = 0;
            $data['apply_limit_monthly'] = 0;
            $data['extra_apply_quantity'] = max((int) ($data['extra_apply_quantity'] ?? 0), 0);
            $data['duration_days'] = max((int) ($data['duration_days'] ?? 30), 1);
        } else {
            $data['job_posts_limit'] = max((int) ($data['job_posts_limit'] ?? 0), 0);
            $data['direct_requests_limit'] = max((int) ($data['direct_requests_limit'] ?? 0), 0);
            $data['apply_limit_monthly'] = 0;
            $data['extra_apply_quantity'] = 0;
            $data['duration_days'] = max((int) ($data['duration_days'] ?? 30), 1);
        }

        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        $plan = \App\Models\Plan::create($data);

        return response()->json([
            'message' => 'Plan created',
            'plan' => $plan,
        ]);
    }

    // View plan
    public function publicPlans()
    {
        $plans = \App\Models\Plan::query()
            ->where('is_active', true)
            ->where('plan_scope', 'client')
            ->get();

        return response()->json($plans);
    }

    // View plan (admin)
    public function plans()
    {
        $plans = \App\Models\Plan::query()
            ->where(function ($query) {
                $query->whereNull('plan_scope')
                    ->orWhere('plan_scope', '!=', 'professional_extra')
                    ->orWhere('name', '!=', 'Extra Apply Pack 25');
            })
            ->get();

        return response()->json($plans);
    }

    public function seedProfessionalPlans()
    {
        $requiredColumns = ['plan_scope', 'apply_limit_monthly', 'extra_apply_quantity', 'is_active'];

        foreach ($requiredColumns as $column) {
            if (! Schema::hasColumn('plans', $column)) {
                return response()->json([
                    'message' => "Missing '{$column}' on plans table. Please run php artisan migrate first.",
                ], 400);
            }
        }

        $now = now();
        $defaults = [
            ['name' => 'Pro Starter', 'price' => 199, 'plan_scope' => 'professional_monthly', 'apply_limit_monthly' => 20, 'extra_apply_quantity' => 0, 'duration_days' => 30],
            ['name' => 'Pro Growth', 'price' => 399, 'plan_scope' => 'professional_monthly', 'apply_limit_monthly' => 50, 'extra_apply_quantity' => 0, 'duration_days' => 30],
            ['name' => 'Extra Apply Pack 10', 'price' => 99, 'plan_scope' => 'professional_extra', 'apply_limit_monthly' => 0, 'extra_apply_quantity' => 10, 'duration_days' => 30],
        ];

        foreach ($defaults as $row) {
            $exists = \App\Models\Plan::query()
                ->where('name', $row['name'])
                ->where('plan_scope', $row['plan_scope'])
                ->exists();

            if (! $exists) {
                \App\Models\Plan::create([
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'plan_scope' => $row['plan_scope'],
                    'job_posts_limit' => 0,
                    'direct_requests_limit' => 0,
                    'duration_days' => $row['duration_days'],
                    'apply_limit_monthly' => $row['apply_limit_monthly'],
                    'extra_apply_quantity' => $row['extra_apply_quantity'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Keep only 3 default professional variations.
        \App\Models\Plan::query()
            ->where('name', 'Extra Apply Pack 25')
            ->where('plan_scope', 'professional_extra')
            ->delete();

        return response()->json(['message' => 'Professional plans added successfully.']);
    }

    // Updata plan
    public function updatePlan(Request $request, $id)
    {
        $plan = \App\Models\Plan::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'plan_scope' => 'sometimes|in:client,professional_monthly,professional_extra',
            'job_posts_limit' => 'sometimes|integer|min:0',
            'direct_requests_limit' => 'sometimes|integer|min:0',
            'duration_days' => 'sometimes|integer|min:1',
            'apply_limit_monthly' => 'sometimes|integer|min:0',
            'extra_apply_quantity' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $data = $request->all();
        $scope = $data['plan_scope'] ?? $plan->plan_scope ?? 'client';
        $data['plan_scope'] = $scope;

        if ($scope === 'professional_monthly') {
            $data['job_posts_limit'] = 0;
            $data['direct_requests_limit'] = 0;
            $data['apply_limit_monthly'] = max((int) ($data['apply_limit_monthly'] ?? $plan->apply_limit_monthly ?? 0), 0);
            $data['extra_apply_quantity'] = 0;
            $data['duration_days'] = max((int) ($data['duration_days'] ?? $plan->duration_days ?? 30), 1);
        } elseif ($scope === 'professional_extra') {
            $data['job_posts_limit'] = 0;
            $data['direct_requests_limit'] = 0;
            $data['apply_limit_monthly'] = 0;
            $data['extra_apply_quantity'] = max((int) ($data['extra_apply_quantity'] ?? $plan->extra_apply_quantity ?? 0), 0);
            $data['duration_days'] = max((int) ($data['duration_days'] ?? $plan->duration_days ?? 30), 1);
        } else {
            $data['job_posts_limit'] = max((int) ($data['job_posts_limit'] ?? $plan->job_posts_limit ?? 0), 0);
            $data['direct_requests_limit'] = max((int) ($data['direct_requests_limit'] ?? $plan->direct_requests_limit ?? 0), 0);
            $data['apply_limit_monthly'] = 0;
            $data['extra_apply_quantity'] = 0;
            $data['duration_days'] = max((int) ($data['duration_days'] ?? $plan->duration_days ?? 30), 1);
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $plan->update($data);

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

    public function payments(Request $request)
    {
        $query = DB::table('processed_payment_transactions as ppt')
            ->join('users as u', 'u.id', '=', 'ppt.user_id')
            ->join('plans as p', 'p.id', '=', 'ppt.plan_id')
            ->select(
                'ppt.id',
                'ppt.tx_ref',
                'ppt.user_id',
                'ppt.plan_id',
                'ppt.processed_at',
                'u.name as user_name',
                'u.email as user_email',
                'p.name as plan_name',
                'p.price as amount'
            );

        if ($request->filled('q')) {
            $search = trim((string) $request->q);
            $query->where(function ($inner) use ($search) {
                $inner->where('ppt.tx_ref', 'like', '%'.$search.'%')
                    ->orWhere('u.name', 'like', '%'.$search.'%')
                    ->orWhere('u.email', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('plan_id')) {
            $query->where('ppt.plan_id', (int) $request->plan_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('ppt.processed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('ppt.processed_at', '<=', $request->date_to);
        }

        $statsQuery = clone $query;
        $todayRevenueQuery = clone $query;
        $paymentsQuery = clone $query;

        $stats = [
            'total_payments' => (int) $statsQuery->count(),
            'total_revenue' => (float) ((clone $query)->sum('p.price') ?? 0),
            'unique_payers' => (int) ((clone $query)->distinct('ppt.user_id')->count('ppt.user_id')),
            'today_revenue' => (float) $todayRevenueQuery->whereDate('ppt.processed_at', now()->toDateString())->sum('p.price'),
        ];

        $payments = $paymentsQuery
            ->orderByDesc('ppt.processed_at')
            ->limit(300)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'tx_ref' => $row->tx_ref,
                    'user_id' => $row->user_id,
                    'user_name' => $row->user_name,
                    'user_email' => $row->user_email,
                    'plan_id' => $row->plan_id,
                    'plan_name' => $row->plan_name,
                    'amount' => (float) $row->amount,
                    'currency' => 'ETB',
                    'status' => 'completed',
                    'processed_at' => $row->processed_at,
                ];
            });

        return response()->json([
            'stats' => $stats,
            'filters' => [
                'q' => $request->q,
                'plan_id' => $request->plan_id,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
            'plans' => \App\Models\Plan::orderBy('name')->get(['id', 'name', 'price']),
            'payments' => $payments,
        ]);
    }

    public function pendingPayments(Request $request)
    {
        $query = \App\Models\Subscription::with(['user:id,name,email', 'plan:id,name,price'])
            ->where('status', 'pending');

        if ($request->filled('q')) {
            $search = trim((string) $request->q);
            $query->where(function ($inner) use ($search) {
                $inner->where('tx_ref', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        $pending = $query->latest()->limit(300)->get()->map(function ($subscription) {
            return [
                'id' => $subscription->id,
                'tx_ref' => $subscription->tx_ref,
                'user_id' => $subscription->user_id,
                'user_name' => $subscription->user->name ?? 'N/A',
                'user_email' => $subscription->user->email ?? 'N/A',
                'plan_id' => $subscription->plan_id,
                'plan_name' => $subscription->plan->name ?? 'N/A',
                'amount' => (float) ($subscription->plan->price ?? 0),
                'currency' => 'ETB',
                'status' => 'pending',
                'created_at' => $subscription->created_at,
            ];
        });

        return response()->json([
            'stats' => [
                'pending_count' => $pending->count(),
                'expected_revenue' => (float) $pending->sum('amount'),
            ],
            'payments' => $pending->values(),
        ]);
    }

    public function stats()
    {
        \App\Models\Contract::autoCompleteExpiredPendingCompletions();

        return response()->json([
            'pending_professionals' => \App\Models\Professional::where('status', 'pending')->count(),
            'active_contracts' => \App\Models\Contract::where('status', 'active')->count(),
            // Exclude rejected professionals from top-level user KPI.
            'total_users' => User::whereDoesntHave('professional', function ($query) {
                $query->where('status', 'rejected');
            })->count(),
            'open_reports' => \App\Models\Report::where('status', 'pending')->count(),
        ]);
    }
}
