<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Professional;

class ProfessionalController extends Controller
{
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

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $professional
        ]);
    }

public function index(Request $request)
{
    $query = \App\Models\Professional::with('user');

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
    $professional = \App\Models\Professional::with('user')->find($id);

    if (!$professional) {
        return response()->json(['message' => 'Professional not found'], 404);
    }

    // get completed jobs
    $completedJobs = \App\Models\Application::where('professional_id', $professional->user_id)
        ->where('status', 'accepted')
        ->whereHas('job', function ($q) {
            $q->where('status', 'completed');
        })
        ->with('job')
        ->get();

    return response()->json([
        'professional' => $professional,
        'completed_jobs' => $completedJobs
    ]);
}
}