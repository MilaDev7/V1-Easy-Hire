<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Contract;
use App\Models\JobPost;
use App\Models\Subscription;

class ClientController extends Controller
{
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
            ->with(['job', 'client', 'professional.professional'])
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

                return [
                    'id' => $contract->id,
                    'title' => $contract->job->title ?? 'Untitled Contract',
                    'client_name' => $contract->client->name ?? 'N/A',
                    'professional_name' => $contract->professional->name ?? 'N/A',
                    'professional_profile_id' => optional($contract->professional->professional)->id,
                    'status' => $contract->status ?? 'N/A',
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

    // ✅ Total Job Posts
    public function jobPostCount()
    {
        $count = JobPost::where('client_id', auth()->id())->count();

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

        $totalAllowed = $subscription->remaining_posts;
        $used = JobPost::where('client_id', auth()->id())
            ->where('status', '!=', 'cancelled')
            ->count();

        return response()->json([
            'remaining' => max($totalAllowed - $used, 0),
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

        // Check if job has any applications
        $applicationCount = \App\Models\Application::where('job_id', $job->id)->count();

        // Delete the job
        $job->delete();

        // Refund if no applications
        if ($applicationCount === 0) {
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
            'message' => 'Job deleted successfully.',
        ]);
    }
}
