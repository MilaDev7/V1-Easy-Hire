<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Contract;
use App\Models\JobPost;
use App\Models\Professional;
use App\Models\ProfessionalPortfolioItem;
use App\Models\Report;
use App\Models\Review;
use App\Services\ApplyCreditService;
use Illuminate\Http\Request;

class ProfessionalController extends Controller
{
    private const REPORT_WARNING_THRESHOLD = 3;

    public function __construct(private ApplyCreditService $applyCreditService) {}

    public function me()
    {
        $user = auth()->user();
        $professional = Professional::where('user_id', $user->id)->first();

        if (! $professional) {
            return response()->json(['message' => 'Professional profile not found'], 404);
        }

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
            $reportCount = Report::where('reported_id', $pro->user_id)->count();

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
                // Only expose aggregate report signals to non-admin consumers.
                'report_count' => (int) $reportCount,
                'reports_count' => (int) $reportCount,
                'report_warning' => $reportCount > self::REPORT_WARNING_THRESHOLD,
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

        $reportCount = Report::where('reported_id', $professional->user_id)->count();
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
            // Only expose aggregate report signals to non-admin consumers.
            'report_count' => (int) $reportCount,
            'reports_count' => (int) $reportCount,
            'report_warning' => $reportCount > self::REPORT_WARNING_THRESHOLD,
            'report_warning_threshold' => self::REPORT_WARNING_THRESHOLD,
            'average_rating' => round($averageRating, 1),
        ]);
    }

    public function jobs(Request $request)
    {
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

        if ($this->applyCreditService->usedApplyCredits($userId) >= ApplyCreditService::MAX_APPLY_CREDITS) {
            return response()->json(['message' => 'Apply limit reached'], 400);
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

        if (! str_contains(strtolower($professional->skill), strtolower($job->skill))) {
            return response()->json(['message' => 'Skill mismatch'], 403);
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

        $remainingApply = max(
            ApplyCreditService::MAX_APPLY_CREDITS - $this->applyCreditService->usedApplyCredits($userId),
            0
        );

        return response()->json([
            'active_contracts' => $active,
            'completed_jobs' => $completed,
            'remaining_apply' => $remainingApply,
        ]);
    }
}
