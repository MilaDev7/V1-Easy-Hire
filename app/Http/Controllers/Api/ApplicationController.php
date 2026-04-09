<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobPost;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ApplicationController extends Controller
{
    /**
     * Cached schema check for applications.source.
     */
    private ?bool $hasApplicationSourceColumn = null;

    /**
     * Prefix used to mark professional-withdrawn applications as non-refundable.
     */
    private const WITHDRAWN_TAG = '[WITHDRAWN_NO_REFUND]';

    /**
     * Maximum application credits for professionals.
     */
    private const MAX_APPLY_CREDITS = 5;

    /**
     * Count consumed apply credits.
     * Consumed = manual-apply applications that are still pending +
     * manual-apply withdrawals marked as non-refundable.
     */
    private function usedApplyCredits(int $professionalId): int
    {
        $pendingQuery = Application::where('professional_id', $professionalId)
            ->where('status', 'pending');

        $withdrawnQuery = Application::where('professional_id', $professionalId)
            ->where('status', 'rejected')
            ->where('cover_letter', 'like', self::WITHDRAWN_TAG.'%');

        if ($this->hasApplicationSourceColumn()) {
            $pendingQuery->where('source', 'apply');
            $withdrawnQuery->where('source', 'apply');
        }

        $pendingApplyApplications = $pendingQuery->count();
        $withdrawnNoRefund = $withdrawnQuery->count();

        return $pendingApplyApplications + $withdrawnNoRefund;
    }

    private function hasApplicationSourceColumn(): bool
    {
        if ($this->hasApplicationSourceColumn === null) {
            $this->hasApplicationSourceColumn = Schema::hasColumn('applications', 'source');
        }

        return $this->hasApplicationSourceColumn;
    }

    /**
     * Professional applies for a job
     */
    public function apply(Request $request, $jobId)
    {

        $user = auth()->user();
        $professional = $user->professional;

        // ✅ 1. check approval (you already have probably)

        // Enforce fixed apply-credit budget.
        if ($this->usedApplyCredits($user->id) >= self::MAX_APPLY_CREDITS) {
            return response()->json([
                'message' => 'Apply credit limit reached',
            ], 403);
        }

        // 1. Count only ACTIVE accepted jobs
        // We check applications where status is 'accepted'
        // AND the linked job status is NOT 'completed' and NOT 'cancelled'
        $activeJobsCount = \App\Models\Application::where('professional_id', $user->id)
            ->where('status', 'accepted')
            ->whereHas('job', function ($query) {
                $query->whereNotIn('status', ['completed', 'cancelled']);
            })
            ->count();

        // 2. Check the limit (3 active jobs)
        if ($activeJobsCount >= 3) {
            return response()->json([
                'message' => 'You reached the maximum limit of active jobs (3). Please complete your current jobs before applying for more.',
            ], 403);
        }

        $alreadyApplied = \App\Models\Application::where('job_id', $jobId)
            ->where('professional_id', $user->id)
            ->exists();

        if ($alreadyApplied) {
            return response()->json([
                'message' => 'You already applied to this job',
            ], 400);
        }

        // 1. Fetch the Job Post first (Fixes the "Undefined status" error)
        $job = JobPost::findOrFail($jobId);
        if ($job->skill !== $professional->skill) {
            return response()->json([
                'message' => 'You cannot apply to jobs outside your skill',
            ], 403);
        }

        // 2. Fetch the Professional profile linked to the logged-in user
        $professionalProfile = Professional::where('user_id', auth()->id())->first();

        // 3. Security Check: Does the professional profile exist?
        if (! $professionalProfile) {
            return response()->json(['message' => 'Professional profile not found.'], 404);
        }

        // 4. Security Check: Is the professional approved by Admin?
        if ($professionalProfile->status !== 'approved') {
            return response()->json([
                'message' => 'Your account is not approved yet. Only approved professionals can apply.',
            ], 403);
        }

        // 5. Security Check: Is the job still open?
        if ($job->status !== 'open') {
            return response()->json(['message' => 'Job is no longer open for applications.'], 400);
        }

        // 5b. Check if job is expired (past deadline)
        if ($job->deadline && $job->deadline < now()->toDateString()) {
            return response()->json(['message' => 'Job deadline has passed.'], 400);
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
            'source' => 'apply',
        ]);

        return response()->json([
            'message' => 'Application submitted successfully',
            'application' => $application,
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
        $applications = $job->applications()
            ->with('professional')
            ->where(function ($query) {
                // Hidden withdrawn applications should not appear to clients.
                $query->whereNull('cover_letter')
                    ->orWhere('cover_letter', 'not like', self::WITHDRAWN_TAG.'%');
            })
            ->get();

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

        // 3. Check if professional has 3 or more active contracts
        $activeContracts = \App\Models\Contract::where('professional_id', $application->professional_id)
            ->where('status', 'active')
            ->count();

        if ($activeContracts >= 3) {
            // Auto-reject the application so the professional gets their slot back
            $application->status = 'rejected';
            $application->save();

            return response()->json([
                'message' => 'Cannot accept. This professional has 3 unfinished jobs. Application has been rejected.',
            ], 400);
        }

        // 4. Accept this application
        $application->status = 'accepted';
        $application->save();

        // 5. Automatically Reject all other applicants for this job
        Application::where('job_id', $job->id)
            ->where('id', '!=', $application->id)
            ->update(['status' => 'rejected']);

        // 5.5 ✅ Create contract
        $client = User::find($job->client_id);
        $professionalUser = User::find($application->professional_id);

        \App\Models\Contract::create([
            'job_id' => $job->id,
            'client_id' => $job->client_id,
            'client_phone' => $client?->phone,
            'professional_id' => $application->professional_id,
            'professional_phone' => $professionalUser?->phone,
            'agreed_price' => $job->budget,
        ]);

        // 6. Update Job status to 'assigned'
        $job->status = 'assigned';
        $job->save();

        return response()->json([
            'message' => 'Professional assigned successfully',
            'application' => $application,
        ]);
    }

    public function reject($id)
    {
        $application = Application::findOrFail($id);
        $job = JobPost::findOrFail($application->job_id);

        if ($job->client_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($application->status !== 'pending') {
            return response()->json(['message' => 'Only pending applications can be rejected'], 400);
        }

        $application->status = 'rejected';
        $application->save();

        return response()->json([
            'message' => 'Application rejected successfully',
            'application' => $application,
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
    public function withdraw($id)
    {
        $application = \App\Models\Application::findOrFail($id);

        // ✅ must be owner
        if ($application->professional_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // ✅ only pending applications can be withdrawn
        if ($application->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending applications can be withdrawn',
            ], 400);
        }

        // No refund on professional withdraw:
        // convert to rejected and tag it as consumed forever for MVP abuse protection.
        $application->status = 'rejected';
        if (! str_starts_with((string) $application->cover_letter, self::WITHDRAWN_TAG)) {
            $application->cover_letter = self::WITHDRAWN_TAG.' '.$application->cover_letter;
        }
        $application->save();

        return response()->json([
            'message' => 'Application withdrawn successfully',
        ]);

    }
}
