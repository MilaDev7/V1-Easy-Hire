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
        'budget' => 'nullable|numeric'
    ]);

    $user = auth()->user();

    // ✅ check subscription
    $subscription = \App\Models\Subscription::where('user_id', $user->id)
        ->where('status', 'active')
        ->first();

    if (!$subscription) {
        return response()->json(['message' => 'You need a subscription'], 403);
    }

    if ($subscription->remaining_posts <= 0) {
        return response()->json(['message' => 'No remaining posts'], 403);
    }

    // ✅ create job
    $job = \App\Models\JobPost::create([
        'client_id' => $user->id,
        'title' => $request->title,
        'description' => $request->description,
        'location' => $request->location,
        'budget' => $request->budget,
    ]);

    // ✅ decrease remaining posts
    $subscription->remaining_posts -= 1;
    $subscription->save();

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
}
