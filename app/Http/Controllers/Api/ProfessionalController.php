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
            'city' => 'nullable|string',
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
            'city',
            'cv',
            'certificate'
        ]));

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $professional
        ]);
    }
}