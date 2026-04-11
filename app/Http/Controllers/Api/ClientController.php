<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Contract;
use App\Models\JobPost;
use App\Models\Subscription;

class ClientController extends Controller
{
    /**
     * Prefix used to mark non-refundable professional withdrawals.
     */
    private const WITHDRAWN_TAG = '[WITHDRAWN_NO_REFUND]';

    public function me()
    {
        $user = auth()->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'profile_photo' => $user->profile_photo
                ? asset('storage/'.$user->profile_photo)
                : asset('images/user1.jpg'),
        ]);
    }

    // ✅ Active Contracts
    public function activeContracts()
    {
        $contracts = Contract::where('client_id', auth()->id())
            ->where('status', 'active')
            ->get();

        return response()->json([
            'count' => $contracts->count(),
            'data' => $contracts,
        ]);
    }

    // ✅ All Contracts
    public function allContracts()
    {
        $userId = auth()->id();

        $contracts = Contract::where('client_id', $userId)
            ->with(['job', 'client', 'professional.professional', 'directRequest'])
            ->latest()
            ->get()
            ->map(function ($contract) use ($userId) {
                // Check if already reviewed
                $hasReview = \App\Models\Review::where('contract_id', $contract->id)
                    ->where('reviewer_id', $userId)
                    ->exists();

                // Check if already reported
                $hasReport = \App\Models\Report::where('contract_id', $contract->id)
                    ->where('reporter_id', $userId)
                    ->exists();

                $title = $contract->job->title
                    ?? $contract->directRequest->title
                    ?? 'Direct Request';

                return [
                    'id' => $contract->id,
                    'title' => $title,
                    'client_name' => $contract->client->name ?? 'N/A',
                    'client_phone' => $contract->client_phone ?? $contract->client->phone ?? 'N/A',
                    'professional_name' => $contract->professional->name ?? 'N/A',
                    'professional_phone' => $contract->professional_phone ?? $contract->professional->phone ?? 'N/A',
                    'professional_profile_id' => optional($contract->professional->professional)->id,
                    'status' => $contract->status ?? 'N/A',
                    'client_confirmed' => ! empty($contract->client_confirmed_at)
                        || (($contract->status ?? null) === 'completed' && ($contract->job->status ?? null) === 'completed'),
                    'created_at' => optional($contract->created_at)->format('Y-m-d') ?? 'N/A',
                    'has_review' => $hasReview,
                    'has_report' => $hasReport,
                ];
            });

        return response()->json($contracts->values());
    }

    // ✅ Applications

    public function applications()
    {
        // Find applications where the related JOB belongs to this client
        $applications = Application::whereHas('job', function ($query) {
            $query->where('client_id', auth()->id());
        })
            ->with(['job', 'professional.professional'])
            ->where(function ($query) {
                // Do not show withdrawn applications in client dashboard.
                $query->whereNull('cover_letter')
                    ->orWhere('cover_letter', 'not like', self::WITHDRAWN_TAG.'%');
            })
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'job_title' => $app->job->title ?? 'N/A',
                    'professional_name' => $app->professional->name ?? 'Unknown',
                    'professional_profile_id' => optional($app->professional->professional)->id,
                    'date_applied' => $app->created_at->format('Y-m-d'),
                    'status' => $app->status,
                ];
            });

        return response()->json($applications);
    }

    // ✅ Active Job Posts (open + assigned)
    public function jobPostCount()
    {
        $count = JobPost::where('client_id', auth()->id())
            ->whereIn('status', ['open', 'assigned'])
            ->count();

        return response()->json([
            'total_job_posts' => $count,
        ]);
    }

    // ✅ Remaining Job Posts
    public function remainingJobPosts()
    {
        $subscription = Subscription::where('user_id', auth()->id())
            ->where('status', 'active')
            ->with('plan')
            ->first();

        if (! $subscription) {
            return response()->json([
                'remaining' => 0,
            ]);
        }

        return response()->json([
            // remaining_posts is already tracked as remaining quota.
            'remaining' => max((int) $subscription->remaining_posts, 0),
        ]);
    }

    public function jobPosts()
    {
        $posts = \App\Models\JobPost::where('client_id', auth()->id())
            ->latest()
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'description' => substr($post->description, 0, 80).'...',
                    'skill' => $post->skill,
                    'status' => $post->status ?? 'open',
                    'location' => $post->location,
                    'start_date' => $post->start_date,
                    'deadline' => $post->deadline,
                    'created_at' => $post->created_at->format('Y-m-d'),
                ];
            });

        return response()->json($posts);
    }

    public function deleteJob($id)
    {
        $job = \App\Models\JobPost::findOrFail($id);

        // Only job owner can delete
        if ($job->client_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent deleting assigned/completed jobs via this endpoint.
        if (in_array($job->status, ['assigned', 'completed'], true)) {
            return response()->json([
                'message' => 'This job cannot be deleted at its current status.',
            ], 400);
        }

        // Idempotency guard: already-cancelled jobs must not refund again.
        if ($job->status === 'cancelled') {
            return response()->json([
                'message' => 'Job already cancelled.',
            ], 400);
        }

        // Refund only when the job has never had any application records at all
        // (including withdrawn ones).
        $hasAnyApplications = Application::where('job_id', $job->id)->exists();

        // Convert still-pending applications to rejected while preserving historical rows.
        // Withdrawn-tagged rows remain untouched.
        Application::where('job_id', $job->id)
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('cover_letter')
                    ->orWhere('cover_letter', 'not like', self::WITHDRAWN_TAG.'%');
            })
            ->update(['status' => 'rejected']);

        // Keep historical records instead of hard delete to preserve refund accounting.
        $job->status = 'cancelled';
        $job->save();

        // Refund only when no applications exist (never applied).
        if (! $hasAnyApplications) {
            $subscription = Subscription::where('user_id', auth()->id())
                ->where('status', 'active')
                ->first();

            if ($subscription) {
                $subscription->increment('remaining_posts');

                return response()->json([
                    'message' => 'Job deleted. 1 post refunded.',
                ]);
            }
        }

        return response()->json([
            'message' => $hasAnyApplications
                ? 'Job cancelled successfully. No refund because applications exist.'
                : 'Job cancelled successfully.',
        ]);
    }
}
