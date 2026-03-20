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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'budget' => 'nullable|numeric',
            'location' => 'required|string|max:255',
        ]);

        $job = JobPost::create([
            'client_id' => Auth::id(), // from token
            'title' => $request->title,
            'description' => $request->description,
            'budget' => $request->budget,
            'location' => $request->location,
        ]);

        return response()->json([
            'message' => 'Job post created successfully',
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
