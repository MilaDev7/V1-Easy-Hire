<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\JobPost;
use App\Models\Professional;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    /**
     * Professional applies for a job
     */
    public function apply(Request $request, $jobId)
    {
        // 1. Fetch the Job Post first (Fixes the "Undefined status" error)
        $job = JobPost::findOrFail($jobId);

        // 2. Fetch the Professional profile linked to the logged-in user
        $professionalProfile = Professional::where('user_id', auth()->id())->first();

        // 3. Security Check: Does the professional profile exist?
        if (!$professionalProfile) {
            return response()->json(['message' => 'Professional profile not found.'], 404);
        }

        // 4. Security Check: Is the professional approved by Admin?
        if ($professionalProfile->status !== 'approved') {
            return response()->json([
                'message' => 'Your account is not approved yet. Only approved professionals can apply.'
            ], 403);
        }

        // 5. Security Check: Is the job still open?
        if ($job->status !== 'open') {
            return response()->json(['message' => 'Job is no longer open for applications.'], 400);
        }

        // 6. Validation
        $request->validate([
            'cover_letter' => 'required|string|min:20',
        ]);

        // 7. Prevent applying twice to the same job
        $exists = Application::where('job_id', $job->id)
            ->where('professional_id', auth()->id())
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You have already applied for this job'], 400);
        }

        // 8. Create the Application
        $application = Application::create([
            'job_id' => $job->id,
            'professional_id' => auth()->id(),
            'cover_letter' => $request->cover_letter,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Application submitted successfully',
            'application' => $application
        ], 201);
    }

    /**
     * Professional views their own applications
     */
    public function myApplications()
    {
        // Assumes relationship 'job' is defined in Application model
        $applications = Application::where('professional_id', auth()->id())
            ->with('job')
            ->get();

        return response()->json($applications);
    }

    /**
     * Client views all applications for one of their jobs
     */
    public function jobApplications($jobId)
    {
        // Ensure the job exists AND belongs to the logged-in client
        $job = JobPost::where('id', $jobId)
            ->where('client_id', auth()->id())
            ->firstOrFail();

        // Assumes relationship 'professional' is defined in JobPost model or through applications
        $applications = $job->applications()->with('professional')->get();

        return response()->json($applications);
    }

    /**
     * Client accepts a professional for a job
     */
    public function accept($id)
    {
        $application = Application::findOrFail($id);
        $job = JobPost::findOrFail($application->job_id);

        // 1. Security: Only the job owner (client) can accept
        if ($job->client_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. Prevent assigning if job is already closed/assigned
        if ($job->status !== 'open') {
            return response()->json(['message' => 'Job is already assigned or closed'], 400);
        }

        // 3. Accept this application
        $application->status = 'accepted';
        $application->save();

        // 4. Automatically Reject all other applicants for this job
        Application::where('job_id', $job->id)
            ->where('id', '!=', $application->id)
            ->update(['status' => 'rejected']);

        // 5. Update Job status to 'assigned'
        $job->status = 'assigned';
        $job->save();

        return response()->json([
            'message' => 'Professional assigned successfully',
            'application' => $application
        ]);
    }

    /**
     * Professional views jobs they were accepted for
     */
    public function myAcceptedJobs()
    {
        $applications = Application::with('job')
            ->where('professional_id', auth()->id())
            ->where('status', 'accepted')
            ->get();

        return response()->json($applications);
    }

    /**
     * Client confirms job is done and leaves a rating
     */
    public function confirmCompletion(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5'
        ]);

        $application = Application::findOrFail($id);
        $job = JobPost::findOrFail($application->job_id);

        // 1. Security: Only job owner
        if ($job->client_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. Logic: Only accepted applications can be rated/completed
        if ($application->status !== 'accepted') {
            return response()->json(['message' => 'This application was not accepted'], 400);
        }

        // 3. Save rating and update job
        $application->rating = $request->rating;
        $application->save();

        $job->status = 'completed';
        $job->save();

        return response()->json([
            'message' => 'Job confirmed as completed and professional rated successfully'
        ]);
    }
}