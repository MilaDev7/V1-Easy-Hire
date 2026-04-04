<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProfessionalStatusMail;


class AdminController extends Controller
{

//Pending Professionals (DEFAULT VIEW)
    public function pendingProfessionals()
    {
        $pros = \App\Models\Professional::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(function ($pro) {
                return [
                    'id' => $pro->id,
                    'name' => $pro->user->name ?? '',
                    'email' => $pro->user->email ?? '',
                    'skill' => $pro->skill,
                    'location' => $pro->location,
                    'cv' => $pro->cv,
                    'certificate' => $pro->certificate,
                    'status' => $pro->status,
                ];
            });

        return response()->json($pros);
    }


    //approveProfessional

    public function approvedProfessionals($id)
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
        return response()->json($professional);
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

public function suspendedUsers($id)
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

        return response()->json($user);
}

    //unsuspendUser

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
    }

    //reports
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

    //user view
    public function users()
    {
        $users = \App\Models\User::select('id', 'name', 'email', 'role', 'is_suspended', 'created_at')
            ->latest()
            ->get();

        return response()->json($users);
    }

    //Veiw all jobs

    public function jobs()
    {
        $jobs = \App\Models\JobPost::with('client')->latest()->get();

        return response()->json([
            'jobs' => $jobs
        ]);
    }


    //View contract
    public function contracts()
    {
        $contracts = \App\Models\Contract::with([
            'client',
            'professional',
            'job'
        ])->latest()->get();

        return response()->json([
            'contracts' => $contracts
        ]);
    } 

    //forceCancelContract

public function forceCancelContract($id)
{
    $contract = \App\Models\Contract::findOrFail($id);

    // ❌ DO NOT cancel completed
    if ($contract->status === 'completed') {
        return response()->json([
            'message' => 'Cannot cancel completed contract'
        ], 400);
    }

    // ❌ already cancelled
    if ($contract->status === 'cancelled') {
        return response()->json([
            'message' => 'Contract already cancelled'
        ], 400);
    }

    // ✅ allow cancel
    $contract->status = 'cancelled';
    $contract->save();

    return response()->json([
        'message' => 'Contract cancelled by admin'
    ]);
}

    //Plan Management (Admin CRUD)

    //CreatePlan
    public function createPlan(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'job_posts_limit' => 'required|integer'
        ]);

        $plan = \App\Models\Plan::create($request->all());

        return response()->json([
            'message' => 'Plan created',
            'plan' => $plan
        ]);
    }

    //View plan
    public function plans()
    {
        $plans = \App\Models\Plan::all();

        return response()->json($plans);
    }

    //Updata plan
    public function updatePlan(Request $request, $id)
    {
        $plan = \App\Models\Plan::findOrFail($id);

        $plan->update($request->all());

        return response()->json([
            'message' => 'Plan updated',
            'plan' => $plan
        ]);
    }

    //Delet plan 

    public function deletePlan($id)
    {
        $plan = \App\Models\Plan::findOrFail($id);

        $plan->delete();

        return response()->json([
            'message' => 'Plan deleted'
        ]);
    }


    //VIEW DELETED USERS

    public function deletedUsers()
    {
        $users = User::onlyTrashed()->get();

        return response()->json($users);
    }

    
    //RESTORE USER

    public function restoreUser($id)
{
    $user = User::withTrashed()->findOrFail($id);

    if (!$user->trashed()) {
        return response()->json([
            'message' => 'User is not deleted'
        ], 400);
    }

    $user->restore();

    return response()->json([
        'message' => 'User restored successfully'
    ]);
}

    public function stats()
    {
        return response()->json([
            'pending_professionals' => \App\Models\Professional::where('status', 'pending')->count(),
            'active_contracts' => \App\Models\Contract::where('status', 'active')->count(),
            'total_users' => User::count(),
            'open_reports' => \App\Models\Report::where('status', 'pending')->count(),
        ]);
    }
}