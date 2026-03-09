<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobPost;
use Illuminate\Support\Facades\Auth;

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
}
