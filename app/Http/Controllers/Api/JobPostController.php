<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobPost;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;

class JobPostController extends Controller
{




    // Client creates a new job post


public function store(Request $request)
{
    $request->validate([
    'title' => 'required|string',
    'description' => 'required|string',
    'location' => 'required|string',
    'budget' => 'nullable|numeric',
    'skill' => 'required|string', // ✅ ADD THIS ONLY
]);

    $user = auth()->user();

    // 1. Get active subscription
$subscription = Subscription::where('user_id', auth()->id())
    ->where('status', 'active')
    ->first();

if (!$subscription) {
    return response()->json([
        'message' => 'You need an active subscription'
    ], 403);
}

// 2. Get plan limit
$plan = $subscription->plan;

// 3. Count how many jobs user already posted
$jobCount = JobPost::where('client_id', auth()->id())
    ->where('status', '!=', 'cancelled')
    ->count();

// 4. Check limit
if ($jobCount >= $plan->job_posts_limit) {
    return response()->json([
        'message' => 'Job post limit reached for your plan'
    ], 403);
}

    // ✅ create job
    $job=JobPost::create([
    'client_id' => auth()->id(),
    'title' => $request->title,
    'description' => $request->description,
    'location' => $request->location,
    'budget' => $request->budget,
    'skill' => $request->skill, // ✅ ADD THIS
    'status' => 'open',
]);
  

    // ✅ THIS IS WHAT YOU WERE MISSING
    return response()->json([
        'message' => 'Job created successfully',
        'job' => $job
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
    if (!$application) {
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
        'message' => 'Job marked as completed'
    ]);
}

public function getMatchingJobs()
{
    $user = auth()->user();

    $professional = $user->professional;

    if (!$professional) {
        return response()->json(['message' => 'Professional profile not found'], 404);
    }

    $jobs = \App\Models\JobPost::where('skill', $professional->skill)
        ->where('location', $professional->location)
        ->where('status', 'open')
        ->get();

    return response()->json($jobs);
}
}
