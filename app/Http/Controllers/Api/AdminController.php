<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function approveProfessional($id)
    {
          $professional = \App\Models\Professional::findOrFail($id);

        if (!$professional) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $professional->status = 'approved';
        $professional->save();

        return response()->json([
            'message' => 'Professional approved'
        ]);
    }

    public function rejectProfessional($id)
    {
        $professional = Professional::find($id);

        if (!$professional) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $professional->status = 'rejected';
        $professional->save();

        return response()->json([
            'message' => 'Professional rejected'
        ]);
    }

    //suspendUser

public function suspendUser($id)
{
    $user = User::findOrFail($id);
   $user->is_suspended = true; 
    $user->save();
    // 🔥 THIS IS THE FIX: This deletes all their current tokens 
    // The next time they try to post a job, their token will be "Invalid"
    $user->tokens()->delete(); 

    return response()->json(['message' => 'User suspended and logged out successfully']);
}

public function unsuspendUser($id)
{
    $user = \App\Models\User::findOrFail($id);

    $user->is_suspended = false;
    $user->save();

    return response()->json(['message' => 'User unsuspended']);
}
//reports
public function reports()
{
    $reports = \App\Models\Report::with(['reporter', 'reported', 'contract'])->get();

    return response()->json($reports);
}

public function resolveReport($id)
{
    $report = \App\Models\Report::findOrFail($id);

    $report->status = 'resolved';
    $report->save();

    return response()->json(['message' => 'Report resolved']);
}
}