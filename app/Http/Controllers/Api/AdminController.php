<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProfessionalStatusMail;

class AdminController extends Controller
{
   public function approveProfessional($id)
 {
    // 1. Fetch professional with the linked user data
    $professional = \App\Models\Professional::with('user')->findOrFail($id);

    $professional->status = 'approved';
    $professional->save();

    // 2. Use the relationship to get the email
    // Make sure your Professional model has a 'user' relationship!
    if ($professional->user) {
        Mail::to($professional->user->email)->send(
            new ProfessionalStatusMail("Your account has been approved. You can now start applying for jobs.")
        );
    }

    // 3. RETURN MUST BE LAST
    return response()->json([
        'message' => 'Professional approved and email sent.'
    ]);
 }

    //rejectProfessional

    public function rejectProfessional($id)
    {
     
          $professional = \App\Models\Professional::findOrFail($id);

        if (!$professional) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $professional->status = 'rejected';
        $professional->save();
    //Mail
    if ($professional->user) {
        Mail::to($professional->user->email)->send(
            new ProfessionalStatusMail("Your account has been rejected. Please contact support.")
        );
    }

        return response()->json([
            'message' => 'Professional rejected'
        ]);

    }

    //suspendUser

public function suspendUser($id)
{
    // 1. Find the user directly
    $user = User::findOrFail($id);
    
    $user->is_suspended = true; 
    $user->save();

    // 2. Kill all active sessions immediately
    $user->tokens()->delete(); 

    // 3. Send Email (Use $user directly, no need for $professional)
    try {
        Mail::to($user->email)->send(
            new ProfessionalStatusMail("Your account has been suspended due to a violation of our terms. All active sessions have been closed.")
        );
    } catch (\Exception $e) {
        \Log::error("Failed to send suspension email: " . $e->getMessage());
    }

    return response()->json([
        'message' => 'User suspended and logged out successfully'
    ]);
}

public function unsuspendUser($id)
{
    // 1. Find the user
    $user = User::findOrFail($id);

    $user->is_suspended = false;
    $user->save();

    // 2. Send Email (Make sure this is BEFORE the return)
    try {
        Mail::to($user->email)->send(
            new ProfessionalStatusMail("Great news! Your account has been restored. You can now log back in and use the system.")
        );
    } catch (\Exception $e) {
        \Log::error("Failed to send unsuspension email: " . $e->getMessage());
    }

    // 3. RETURN MUST BE LAST
    return response()->json([
        'message' => 'User unsuspended successfully'
    ]);
}//reports
public function reports()
{
    $reports = \App\Models\Report::with([
        'contract',
        'reporter:id,name,email',
        'reported:id,name,email'
    ])->latest()->get();

    return response()->json($reports);
}


//resolveReport

public function resolveReport(Request $request, $id)
{
    $report = \App\Models\Report::findOrFail($id);

    // optional action from admin
    if ($request->action === 'suspend') {
        $user = \App\Models\User::find($report->reported_id);
        $user->is_suspended = true;
        $user->tokens()->delete();
        $user->save();
    }

    if ($request->action === 'cancel_contract') {
        $contract = \App\Models\Contract::find($report->contract_id);
        $contract->status = 'cancelled';
        $contract->save();
    }

    $report->status = 'resolved';
    $report->save();

    return response()->json([
        'message' => 'Report resolved with action: ' . ($request->action ?? 'none')
    ]);
}
}