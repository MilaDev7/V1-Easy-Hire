<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class JobPostController extends Controller
{
    // Client creates a new job post

    public function store(Request $request)
    {
        Log::info('Job post request', $request->all());

        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string',
            'budget' => 'nullable|numeric',
            'skill' => 'required|string',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after:start_date',
        ]);

        $user = auth()->user();

        // 1. Get active subscription
        $subscription = Subscription::where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if (! $subscription) {
            return response()->json([
                'message' => 'You need an active subscription',
            ], 403);
        }

        // Enforce expiry even when stale records are still marked as active.
        if ($subscription->expires_at && Carbon::parse($subscription->expires_at)->isPast()) {
            $subscription->status = 'expired';
            $subscription->save();

            return response()->json([
                'message' => 'Your subscription has expired. Please renew your plan.',
            ], 403);
        }

        // 2. Check remaining post quota directly.
        if (($subscription->remaining_posts ?? 0) < 1) {
            return response()->json([
                'message' => 'Job post limit reached for your plan',
            ], 403);
        }

        // ✅ create job
        $job = JobPost::create([
            'client_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'budget' => $request->budget,
            'skill' => $request->skill,
            'status' => 'open',
            'start_date' => $request->start_date,
            'deadline' => $request->deadline,
        ]);

        Log::info('Job created', ['job_id' => $job->id, 'client_id' => $job->client_id]);

        // ✅ Decrement remaining posts
        $subscription->decrement('remaining_posts');

        // ✅ THIS IS WHAT YOU WERE MISSING
        return response()->json([
            'message' => 'Job created successfully',
            'job' => $job,
        ], 201);
    }

    public function complete($id)
    {
        $job = \App\Models\JobPost::findOrFail($id);

        // 🔍 Find accepted application
        $application = \App\Models\Application::where('job_id', $job->id)
            ->where('professional_id', auth()->id())
            ->where('status', 'accepted')
            ->first();

        // ❌ If not assigned to this professional
        if (! $application) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // ❌ Only allow if job is assigned
        if ($job->status !== 'assigned') {
            return response()->json(['message' => 'Job not in assigned state'], 400);
        }

        // ✅ Mark as completed
        $job->status = 'completed';
        $job->save();

        return response()->json([
            'message' => 'Job marked as completed',
        ]);
    }

    public function getMatchingJobs()
    {
        JobPost::autoExpireOpenJobs();

        $user = auth()->user();

        $professional = $user->professional;

        if (! $professional) {
            return response()->json(['message' => 'Professional profile not found'], 404);
        }

        $jobs = \App\Models\JobPost::where('skill', $professional->skill)
            ->where('location', $professional->location)
            ->where('status', 'open')
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'description' => $job->description,
                    'budget' => $job->budget,
                    'skill' => $job->skill,
                    'location' => $job->location,
                    'status' => $job->status,
                    'start_date' => $job->start_date,
                    'deadline' => $job->deadline,
                ];
            });

        return response()->json($jobs);
    }
}
