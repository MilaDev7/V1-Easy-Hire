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
    $user = auth()->user();

    // 1. Check role (must be client)
    if ($user->role !== 'client') {
        return response()->json(['message' => 'Only clients can post jobs'], 403);
    }
$subscription = Subscription::where('user_id', $user->id)->first();

if (!$subscription) {
    return response()->json([
        'message' => 'You need a subscription'
    ], 403);
}

// check expiry
if ($subscription->expires_at && $subscription->expires_at < now()) {
    return response()->json([
        'message' => 'Subscription expired'
    ], 403);
}

// check remaining posts
if ($subscription->remaining_posts <= 0) {
    return response()->json([
        'message' => 'No remaining job posts'
    ], 403);
}
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
