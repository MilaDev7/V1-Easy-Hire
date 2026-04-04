<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Contract;
use App\Models\JobPost;
use Illuminate\Http\Request;
use App\Models\Professional;

class ProfessionalController extends Controller
{
    public function me()
    {
        $user = auth()->user();
        $professional = Professional::where('user_id', $user->id)->first();

        if (!$professional) {
            return response()->json(['message' => 'Professional profile not found'], 404);
        }

        return response()->json([
            'id' => $professional->id,
            'name' => $user->name,
            'profile_photo' => $user->profile_photo,
            'skill' => $professional->skill,
            'location' => $professional->location,
            'approval_status' => $professional->status,
            'rate' => $professional->average_rating,
            'total_reviews' => $professional->total_reviews,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'skill' => 'required|string',
            'experience' => 'required|integer',
            'bio' => 'nullable|string',
            'location' => 'nullable|string',
            'cv' => 'nullable|string',
            'certificate' => 'nullable|string',
        ]);

        $professional = Professional::where('user_id', auth()->id())->first();

        if (!$professional) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $professional->update($request->only([
            'skill',
            'experience',
            'bio',
            'location',
            'cv',
            'certificate'
        ]));

        $professional->status = 'pending';
        $professional->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $professional
        ]);
    }

    public function index(Request $request)
    {
        $query = Professional::with('user');

        if ($request->skill) {
            $query->where('skill', 'LIKE', '%' . $request->skill . '%');
        }

        if ($request->location) {
            $query->where('location', 'LIKE', '%' . $request->location . '%');
        }

        $professionals = $query->get();

        return response()->json([
            'data' => $professionals
        ]);
    }

    public function show($id)
    {
        $professional = Professional::with('user')->find($id);

        if (!$professional) {
            return response()->json(['message' => 'Professional not found'], 404);
        }

        $completedJobs = Application::where('professional_id', $professional->user_id)
            ->where('status', 'accepted')
            ->whereHas('job', function ($query) {
                $query->where('status', 'completed');
            })
            ->with('job')
            ->get();

        return response()->json([
            'professional' => $professional,
            'completed_jobs' => $completedJobs
        ]);
    }

    public function jobs(Request $request)
    {
        $userId = auth()->id();
        $professional = Professional::where('user_id', $userId)->first();
        $query = JobPost::query()->where('status', 'open');

        if ($request->skill) {
            $query->where('skill', 'LIKE', '%' . $request->skill . '%');
        }

        if ($request->location) {
            $query->where('location', 'LIKE', '%' . $request->location . '%');
        }

        $appliedJobIds = Application::where('professional_id', $userId)
            ->pluck('job_id')
            ->all();

        $jobs = $query
            ->latest()
            ->get()
            ->map(function (JobPost $job) use ($appliedJobIds, $professional) {
                $jobSkill = strtolower(trim((string) $job->skill));
                $professionalSkill = strtolower(trim((string) ($professional?->skill ?? '')));
                $skillMatch = $professionalSkill !== '' && str_contains($professionalSkill, $jobSkill);

                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'description' => $job->description,
                    'budget' => $job->budget,
                    'skills' => $job->skill,
                    'status' => $job->status,
                    'location' => $job->location,
                    'has_applied' => in_array($job->id, $appliedJobIds, true),
                    'skill_match' => $skillMatch,
                ];
            });

        return response()->json([
            'data' => $jobs
        ]);
    }

    public function apply(Request $request)
    {
        $userId = auth()->id();
        $request->validate([
            'job_id' => 'required|exists:job_posts,id',
        ]);

        $job = JobPost::findOrFail($request->job_id);

        $exists = Application::where('job_id', $job->id)
            ->where('professional_id', $userId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Already applied'], 400);
        }

        $count = Application::where('professional_id', $userId)
            ->where('status', 'pending')
            ->count();

        if ($count >= 5) {
            return response()->json(['message' => 'Apply limit reached'], 400);
        }

        $professional = Professional::where('user_id', $userId)->first();

        if (!$professional) {
            return response()->json(['message' => 'Profile missing'], 404);
        }

        if ($professional->status !== 'approved') {
            return response()->json(['message' => 'Account not approved'], 403);
        }

        if ($job->status !== 'open') {
            return response()->json(['message' => 'Job is not open'], 400);
        }

        if (!str_contains(strtolower($professional->skill), strtolower($job->skill))) {
            return response()->json(['message' => 'Skill mismatch'], 403);
        }

        Application::create([
            'job_id' => $job->id,
            'professional_id' => $userId,
            'cover_letter' => 'Dashboard quick apply placeholder.',
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Applied successfully']);
    }

    public function myApplications()
    {
        $apps = Application::where('professional_id', auth()->id())
            ->with('job')
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'job_id' => $app->job_id,
                    'job_title' => $app->job->title ?? '',
                    'status' => $app->status
                ];
            });

        return response()->json(['data' => $apps]);
    }

    public function withdraw(Request $request)
    {
        $app = Application::where('id', $request->id)
            ->where('professional_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$app) {
            return response()->json(['message' => 'Cannot withdraw'], 400);
        }

        $app->delete();

        return response()->json(['message' => 'Withdrawn']);
    }

    public function myContracts()
    {
        $contracts = Contract::where('professional_id', auth()->id())
            ->with(['job', 'client'])
            ->latest()
            ->get()
            ->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'job_title' => $contract->job->title ?? 'Untitled Job',
                    'client_name' => $contract->client->name ?? 'N/A',
                    'budget' => $contract->agreed_price,
                    'status' => $contract->status,
                    'created_at' => $contract->created_at,
                ];
            });

        return response()->json(['data' => $contracts]);
    }

    public function completeContract(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $contract = Contract::where('id', $request->id)
            ->where('professional_id', auth()->id())
            ->first();

        if (!$contract) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if ($contract->status !== 'active') {
            return response()->json(['message' => 'Only active contracts can be completed'], 400);
        }

        $contract->status = 'completed';
        $contract->save();

        return response()->json(['message' => 'Completed']);
    }

    public function stats()
    {
        $userId = auth()->id();

        $active = Contract::where('professional_id', $userId)
            ->where('status', 'active')
            ->count();

        $completed = Contract::where('professional_id', $userId)
            ->where('status', 'completed')
            ->count();

        $applications = Application::where('professional_id', $userId)
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'active_contracts' => $active,
            'completed_jobs' => $completed,
            'remaining_apply' => max(5 - $applications, 0)
        ]);
    }
}
