<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Contract;
use App\Models\JobPost;
use App\Models\Plan;
use App\Models\Professional;
use App\Models\ProfessionalPortfolioItem;
use App\Models\Report;
use App\Models\Review;
use App\Services\ApplyCreditService;
use App\Services\NotificationService;
use App\Services\Chapa;
use Illuminate\Http\Request;

class ProfessionalController extends Controller
{
    public function __construct(
        private ApplyCreditService $applyCreditService,
        private NotificationService $notificationService
    ) {}

    public function me()
    {
        $user = auth()->user();
        $professional = Professional::where('user_id', $user->id)->first();

        if (! $professional) {
            return response()->json(['message' => 'Professional profile not found'], 404);
        }

        $reviews = Review::where('reviewed_id', $user->id)
            ->with(['reviewer:id,name', 'contract.job:id,title', 'contract.directRequest:id,title'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($review) {
                $contractTitle = $review->contract?->job?->title
                    ?? $review->contract?->directRequest?->title
                    ?? 'Direct Request';

                return [
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'contract_title' => $contractTitle,
                    'client_name' => $review->reviewer?->name ?? 'N/A',
                    'created_at' => $review->created_at,
                ];
            });

        $reportCount = Report::where('reported_id', $user->id)->count();
        $reportStatus = Report::where('reported_id', $user->id)
            ->where('status', 'pending')
            ->exists() ? 'under_review' : 'resolved';

        return response()->json([
            'id' => $professional->id,
            'name' => $user->name,
            'profile_photo' => $professional->profile_photo
                ? asset('storage/'.$professional->profile_photo)
                : ($user->profile_photo ? asset('storage/'.$user->profile_photo) : asset('images/user1.jpg')),
            'skill' => $professional->skill,
            'location' => $professional->location,
            'approval_status' => $professional->status,
            'rate' => $professional->average_rating,
            'total_reviews' => $professional->total_reviews,
            'reviews' => $reviews->values(),
            'report_count' => (int) $reportCount,
            'report_status' => $reportStatus,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'skill' => 'required|string',
            'experience' => 'required|integer',
            'bio' => 'nullable|string',
            'location' => 'nullable|string',
            'cv' => 'nullable|string',
            'certificate' => 'nullable|string',
        ]);

        $professional = Professional::where('user_id', auth()->id())->first();

        if (! $professional) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $professional->update($request->only([
            'skill',
            'experience',
            'bio',
            'location',
            'cv',
            'certificate',
        ]));

        $professional->status = 'pending';
        $professional->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $professional,
        ]);
    }

    public function index(Request $request)
    {
        $query = Professional::with('user')->where('status', 'approved');

        if ($request->skill) {
            $query->where('skill', 'LIKE', '%'.$request->skill.'%');
        }

        if ($request->location) {
            $query->where('location', 'LIKE', '%'.$request->location.'%');
        }

        $professionals = $query->get()->map(function ($pro) {
            $reviews = Review::where('reviewed_id', $pro->user_id)->with('reviewer:id,name')->get();
            $activeReportsCount = Report::where('reported_id', $pro->user_id)
                ->where('status', 'pending')
                ->count();
            $issuesRecordedCount = Report::where('reported_id', $pro->user_id)
                ->where('action_taken', '!=', 'none')
                ->count();

            return [
                'id' => $pro->id,
                'user_id' => $pro->user_id,
                'name' => $pro->user ? $pro->user->name : 'N/A',
                'email' => $pro->user ? $pro->user->email : 'N/A',
                'profile_photo' => $pro->profile_photo,
                'skill' => $pro->skill,
                'location' => $pro->location,
                'experience' => $pro->experience,
                'bio' => $pro->bio,
                'average_rating' => $reviews->avg('rating') ?? 0,
                'reviews_count' => $reviews->count(),
                'reviews' => $reviews->take(3)->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'rating' => $r->rating,
                        'comment' => $r->comment,
                        'reviewer_name' => $r->reviewer ? $r->reviewer->name : 'Anonymous',
                        'created_at' => $r->created_at,
                    ];
                }),
                'active_reports_count' => (int) $activeReportsCount,
                'issues_recorded_count' => (int) $issuesRecordedCount,
            ];
        });

        return response()->json([
            'data' => $professionals,
        ]);
    }

    public function show($id)
    {
        $professional = Professional::with('user')->find($id);

        if (! $professional) {
            return response()->json(['message' => 'Professional not found'], 404);
        }

        $completedJobs = Application::where('professional_id', $professional->user_id)
            ->where('status', 'accepted')
            ->whereHas('job', function ($query) {
                $query->where('status', 'completed');
            })
            ->with('job')
            ->get();

        $reviews = Review::where('reviewed_id', $professional->user_id)
            ->with('reviewer:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'rating' => $r->rating,
                    'comment' => $r->comment,
                    'reviewer_name' => $r->reviewer ? $r->reviewer->name : 'Anonymous',
                    'created_at' => $r->created_at,
                ];
            });

        $activeReportsCount = Report::where('reported_id', $professional->user_id)
            ->where('status', 'pending')
            ->count();
        $issuesRecordedCount = Report::where('reported_id', $professional->user_id)
            ->where('action_taken', '!=', 'none')
            ->count();
        $averageRating = $reviews->count() > 0 ? $reviews->avg('rating') : 0;
        $portfolioItems = ProfessionalPortfolioItem::where('professional_id', $professional->id)
            ->latest()
            ->get()
            ->map(function (ProfessionalPortfolioItem $item) {
                return [
                    'id' => $item->id,
                    'image_url' => asset('storage/'.$item->image_path),
                    'description' => $item->description,
                    'linked_job_id' => $item->job_id,
                    'created_at' => $item->created_at,
                ];
            });

        return response()->json([
            'professional' => $professional,
            'completed_jobs' => $completedJobs,
            'portfolio_items' => $portfolioItems,
            'reviews' => $reviews,
            'reviews_count' => $reviews->count(),
            'active_reports_count' => (int) $activeReportsCount,
            'issues_recorded_count' => (int) $issuesRecordedCount,
            'average_rating' => round($averageRating, 1),
        ]);
    }

    public function publicProfileSummary($id)
    {
        $professional = Professional::find($id);

        if (! $professional) {
            return response()->json(['message' => 'Professional not found'], 404);
        }

        $activeReportsCount = Report::where('reported_id', $professional->user_id)
            ->where('status', 'pending')
            ->count();
        $issuesRecordedCount = Report::where('reported_id', $professional->user_id)
            ->where('action_taken', '!=', 'none')
            ->count();

        return response()->json([
            'active_reports_count' => (int) $activeReportsCount,
            'issues_recorded_count' => (int) $issuesRecordedCount,
        ]);
    }

    public function myReports()
    {
        $userId = auth()->id();

        $reports = Report::query()
            ->where('reported_id', $userId)
            ->latest()
            ->get()
            ->map(function ($report) {
                $status = $report->status === 'resolved' ? 'resolved' : 'pending';
                return [
                    'id' => $report->id,
                    'reason' => $report->reason,
                    'status' => $status,
                    'action_taken' => $report->action_taken ?: 'none',
                    'created_at' => $report->created_at,
                ];
            });

        return response()->json([
            'total_reports' => $reports->count(),
            'active_reports_count' => $reports->where('status', 'pending')->count(),
            'resolved_reports_count' => $reports->where('status', 'resolved')->count(),
            'active_reports' => $reports->where('status', 'pending')->values(),
            'resolved_reports' => $reports->where('status', 'resolved')->values(),
        ]);
    }

    public function jobs(Request $request)
    {
        JobPost::autoExpireOpenJobs();

        $userId = auth()->id();
        $professional = Professional::where('user_id', $userId)->first();
        $query = JobPost::query()->where('status', 'open');

        if ($request->skill) {
            $query->where('skill', 'LIKE', '%'.$request->skill.'%');
        }

        if ($request->location) {
            $query->where('location', 'LIKE', '%'.$request->location.'%');
        }

        // Exclude professional-withdrawn rows so withdrawn jobs can be applied again.
        $appliedJobIds = Application::where('professional_id', $userId)
            ->where(function ($query) {
                $query->where('status', '!=', 'rejected')
                    ->orWhereNull('cover_letter')
                    ->orWhere('cover_letter', 'not like', ApplyCreditService::WITHDRAWN_TAG.'%');
            })
            ->pluck('job_id')
            ->all();

        $jobs = $query
            ->latest()
            ->get()
            ->map(function (JobPost $job) use ($appliedJobIds, $professional) {
                $jobSkill = strtolower(trim((string) $job->skill));
                $professionalSkill = strtolower(trim((string) ($professional?->skill ?? '')));
                $skillMatch = $professionalSkill !== '' && str_contains($professionalSkill, $jobSkill);

                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'description' => $job->description,
                    'budget' => $job->budget,
                    'skills' => $job->skill,
                    'status' => $job->status,
                    'location' => $job->location,
                    'start_date' => $job->start_date,
                    'deadline' => $job->deadline,
                    'has_applied' => in_array($job->id, $appliedJobIds, true),
                    'skill_match' => $skillMatch,
                ];
            });

        return response()->json([
            'data' => $jobs,
        ]);
    }

    public function apply(Request $request)
    {
        JobPost::autoExpireOpenJobs();

        $userId = auth()->id();
        $request->validate([
            'job_id' => 'required|exists:job_posts,id',
            'cover_letter' => 'nullable|string|max:1000',
        ]);

        $job = JobPost::findOrFail($request->job_id);

        // Allow re-apply only when previous row was professional-withdrawn.
        $exists = Application::where('job_id', $job->id)
            ->where('professional_id', $userId)
            ->where(function ($query) {
                $query->where('status', '!=', 'rejected')
                    ->orWhereNull('cover_letter')
                    ->orWhere('cover_letter', 'not like', ApplyCreditService::WITHDRAWN_TAG.'%');
            })
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Already applied'], 400);
        }

        $professional = Professional::where('user_id', $userId)->first();

        if (! $professional) {
            return response()->json(['message' => 'Profile missing'], 404);
        }

        if ($professional->status !== 'approved') {
            return response()->json(['message' => 'You are not approved yet. The admin is reviewing your info.'], 403);
        }

        if ($job->status !== 'open') {
            return response()->json(['message' => 'Job is not open'], 400);
        }

        if ($job->deadline && $job->deadline < now()->toDateString()) {
            $job->status = 'expired';
            $job->save();

            return response()->json(['message' => 'Job deadline has passed.'], 400);
        }

        if (! str_contains(strtolower($professional->skill), strtolower($job->skill))) {
            return response()->json(['message' => 'Skill mismatch'], 403);
        }

        $consumeResult = $this->applyCreditService->consumeApply($userId);
        if (! ($consumeResult['success'] ?? false)) {
            return response()->json(['message' => 'Apply limit reached'], 400);
        }

        Application::create([
            'job_id' => $job->id,
            'professional_id' => $userId,
            'cover_letter' => $request->cover_letter ?? 'Dashboard quick apply placeholder.',
            'status' => 'pending',
            'source' => 'apply',
        ]);

        return response()->json(['message' => 'Applied successfully']);
    }

    public function myApplications()
    {
        $apps = Application::where('professional_id', auth()->id())
            ->with('job')
            ->get()
            ->map(function ($app) {
                $isWithdrawn = $app->status === 'rejected'
                    && str_starts_with((string) $app->cover_letter, ApplyCreditService::WITHDRAWN_TAG);

                return [
                    'id' => $app->id,
                    'job_id' => $app->job_id,
                    'job_title' => $app->job->title ?? '',
                    // Show explicit status label for UX while reusing existing schema.
                    'status' => $isWithdrawn ? 'withdrawn' : $app->status,
                ];
            });

        return response()->json(['data' => $apps]);
    }

    public function withdraw(Request $request)
    {
        $app = Application::where('id', $request->id)
            ->where('professional_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (! $app) {
            return response()->json(['message' => 'Cannot withdraw'], 400);
        }

        // No refund on professional withdrawal:
        // keep a tagged rejected row so the credit stays consumed.
        $app->status = 'rejected';
        if (! str_starts_with((string) $app->cover_letter, ApplyCreditService::WITHDRAWN_TAG)) {
            $app->cover_letter = ApplyCreditService::WITHDRAWN_TAG.' '.$app->cover_letter;
        }
        $app->save();

        return response()->json(['message' => 'Withdrawn. Apply credit remains consumed.']);
    }

    public function myContracts()
    {
        Contract::autoCompleteExpiredPendingCompletions();

        $contracts = Contract::where('professional_id', auth()->id())
            ->with(['job', 'client', 'professional', 'directRequest'])
            ->latest()
            ->get()
            ->map(function ($contract) {
                $userId = auth()->id();
                $hasReview = \App\Models\Review::where('contract_id', $contract->id)
                    ->where('reviewer_id', $userId)
                    ->exists();
                $hasReport = \App\Models\Report::where('contract_id', $contract->id)
                    ->where('reporter_id', $userId)
                    ->exists();

                $title = $contract->job->title
                    ?? $contract->directRequest->title
                    ?? 'Direct Request';

                return [
                    'id' => $contract->id,
                    'job_title' => $title,
                    'client_name' => $contract->client->name ?? 'N/A',
                    'client_phone' => $contract->client_phone ?? $contract->client->phone ?? 'N/A',
                    'professional_phone' => $contract->professional_phone ?? $contract->professional->phone ?? 'N/A',
                    'budget' => $contract->agreed_price,
                    'status' => $contract->status,
                    'created_at' => $contract->created_at,
                    'has_review' => $hasReview,
                    'has_report' => $hasReport,
                ];
            });

        return response()->json(['data' => $contracts]);
    }

    public function completeContract(Request $request)
    {
        Contract::autoCompleteExpiredPendingCompletions();

        $request->validate([
            'id' => 'required|integer',
        ]);

        $contract = Contract::where('id', $request->id)
            ->where('professional_id', auth()->id())
            ->first();

        if (! $contract) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if ($contract->status !== 'active') {
            return response()->json(['message' => 'Only active contracts can be completed'], 400);
        }

        $contract->status = 'pending_completion';
        $contract->save();

        $title = $contract->job?->title ?? $contract->directRequest?->title ?? 'Contract Work';
        $this->notificationService->send(
            (int) $contract->client_id,
            'completion_requested',
            'Work marked as completed',
            'Professional marked work as completed: '.$title,
            '/client/dashboard?view=contracts',
            ['contract_id' => $contract->id]
        );

        return response()->json(['message' => 'Marked as pending completion']);
    }

    public function stats()
    {
        Contract::autoCompleteExpiredPendingCompletions();

        $userId = auth()->id();

        $active = Contract::where('professional_id', $userId)
            ->where('status', 'active')
            ->count();

        $completed = Contract::where('professional_id', $userId)
            ->where('status', 'completed')
            ->count();

        $wallet = $this->applyCreditService->walletState($userId);

        return response()->json([
            'active_contracts' => $active,
            'completed_jobs' => $completed,
            'remaining_apply' => $wallet['remaining_applies'] ?? ($wallet['remaining_total'] ?? 0),
            'remaining_applies' => $wallet['remaining_applies'] ?? ($wallet['remaining_total'] ?? 0),
            'monthly_limit' => $wallet['monthly_limit'] ?? 0,
            'expiry_date' => $wallet['expiry_date'] ?? null,
            'days_left' => $wallet['days_left'] ?? 0,
        ]);
    }

    public function applyPlanSummary()
    {
        $wallet = $this->applyCreditService->walletState(auth()->id());

        return response()->json([
            'current_plan_id' => $wallet['current_plan_id'],
            'current_plan_name' => $wallet['current_plan_name'] ?? 'Free Plan',
            'current_plan_duration_days' => $wallet['current_plan_duration_days'] ?? null,
            'monthly_limit' => $wallet['monthly_limit'] ?? 0,
            'remaining_applies' => $wallet['remaining_applies'] ?? ($wallet['remaining_total'] ?? 0),
            'remaining_total' => $wallet['remaining_total'] ?? 0,
            'expiry_date' => $wallet['expiry_date'] ?? null,
            'days_left' => $wallet['days_left'] ?? 0,
        ]);
    }

    public function applyPlans()
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->whereIn('plan_scope', ['professional_monthly', 'professional_extra'])
            ->where(function ($query) {
                $query->where('plan_scope', '!=', 'professional_extra')
                    ->orWhere('name', '!=', 'Extra Apply Pack 25');
            })
            ->orderBy('price')
            ->get()
            ->map(function (Plan $plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'price' => (float) $plan->price,
                    'plan_scope' => $plan->plan_scope,
                    'apply_limit_monthly' => (int) $plan->apply_limit_monthly,
                    'extra_apply_quantity' => (int) $plan->extra_apply_quantity,
                    'duration_days' => (int) ($plan->duration_days ?? 30),
                ];
            });

        return response()->json(['data' => $plans]);
    }

    public function buyApplyPlan(Request $request, $id)
    {
        $user = auth()->user();
        $plan = Plan::where('id', $id)
            ->where('is_active', true)
            ->whereIn('plan_scope', ['professional_monthly', 'professional_extra'])
            ->first();

        if (! $plan) {
            return response()->json(['message' => 'Plan not found'], 404);
        }

        $namePrefix = strtolower(trim((string) $user->name));
        $namePrefix = preg_replace('/[^a-z0-9]+/', '_', $namePrefix);
        $namePrefix = trim((string) $namePrefix, '_');
        if ($namePrefix === '') {
            $namePrefix = 'user_'.$user->id;
        }

        $txRef = Chapa::generateReference($namePrefix);
        $chapa = new Chapa;

        $response = $chapa->initializePayment([
            'amount' => $plan->price,
            'currency' => 'ETB',
            'email' => $user->email,
            'first_name' => $user->name,
            'tx_ref' => $txRef,
            'callback_url' => url('/api/chapa/payment-success'),
            'return_url' => url('/payment-success?tx_ref='.$txRef),
            'meta' => [
                'plan_id' => $plan->id,
                'user_id' => $user->id,
            ],
        ]);

        if (! is_array($response) || ($response['status'] ?? null) !== 'success') {
            return response()->json([
                'message' => $response['message'] ?? 'Payment initialization failed',
            ], 500);
        }

        return response()->json([
            'redirect_url' => $response['data']['checkout_url'] ?? null,
            'tx_ref' => $txRef,
        ]);
    }
}
