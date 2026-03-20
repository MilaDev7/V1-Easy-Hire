<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\JobPost;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    // Professional applies for a job
    public function apply(Request $request, $jobId)
    {

    $professional = \App\Models\Professional::where('user_id', auth()->id())->first();

if ($professional->status !== 'approved') {
    return response()->json([
        'message' => 'Your account is not approved yet'
    ], 403);
}
        $request->validate([
            'cover_letter' => 'required|string',
        ]);

        $job = JobPost::findOrFail($jobId);

        // Prevent applying twice
        $exists = Application::where('job_id', $job->id)
            ->where('professional_id', Auth::id())
            ->first();

        if ($exists) {
            return response()->json(['message' => 'You have already applied for this job'], 400);
        }

        $application = Application::create([
            'job_id' => $job->id,
            'professional_id' => Auth::id(),
            'cover_letter' => $request->cover_letter,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Application submitted successfully',
            'application' => $application
        ], 201);
    }

    // Professional can view their applications
    public function myApplications()
    {
        $applications = Application::where('professional_id', Auth::id())
            ->with('job')
            ->get();

        return response()->json($applications);
    }

    // Client can view applications for their job
    public function jobApplications($jobId)
    {
    
        $job = JobPost::where('id', $jobId)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        $applications = $job->applications()->with('professional')->get();

        return response()->json($applications);
    }

    // Client updates application status
    public function updateStatus(Request $request, $applicationId)
    {
        $request->validate([
            'status' => 'required|in:pending,accepted,rejected',
        ]);

        $application = Application::findOrFail($applicationId);

        // Only job owner can update
        if ($application->job->client_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $application->status = $request->status;
        $application->save();

        return response()->json([
            'message' => 'Application status updated',
            'application' => $application
        ]);
    }

    public function accept($id)
{
    $application = \App\Models\Application::findOrFail($id);

    $job = $application->job;

    // 🔒 Make sure only job owner (client) can accept
    if ($job->client_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // ✅ Accept selected application
    $application->status = 'accepted';
    $application->save();

    // ❌ Reject all other applications for this job
    \App\Models\Application::where('job_id', $job->id)
        ->where('id', '!=', $application->id)
        ->update(['status' => 'rejected']);

    // 🔄 Update job status
    $job->status = 'assigned';
    $job->save();

    return response()->json([
        'message' => 'Application accepted successfully'
    ]);
}

public function myAcceptedJobs()
{
    $applications = \App\Models\Application::with('job')
        ->where('professional_id', auth()->id())
        ->where('status', 'accepted')
        ->get();

    return response()->json($applications);
}

public function confirmCompletion(Request $request, $id)
{
    $request->validate([
        'rating' => 'required|integer|min:1|max:5'
    ]);

    $application = \App\Models\Application::findOrFail($id);
    $job = $application->job;

    // 🔒 Only job owner (client)
    if ($job->client_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // ❌ Only accepted application can be confirmed
    if ($application->status !== 'accepted') {
        return response()->json(['message' => 'Invalid application'], 400);
    }

    // ❌ Job must be completed by professional first
    if ($job->status !== 'completed') {
        return response()->json(['message' => 'Job not completed yet'], 400);
    }

    // ✅ Save rating
    $application->rating = $request->rating;
    $application->save();

    return response()->json([
        'message' => 'Job confirmed and rated successfully'
    ]);
}
}
