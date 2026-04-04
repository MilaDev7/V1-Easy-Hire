<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobPostController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\ProfessionalController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ClientController;


// Public Routes (No Token Required)

Route::post('/register/client', [AuthController::class, 'registerClient']);
Route::post('/register/professional', [AuthController::class, 'registerProfessional']);
Route::post('/login', [AuthController::class, 'login']);

// Professionals are usually public so clients can browse them
Route::get('/professionals', [ProfessionalController::class, 'index']);
Route::get('/professionals/{id}', [ProfessionalController::class, 'show']);

// Professional Setup
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/pro/profile-update', [ProfileController::class, 'updateProProfile']);
    Route::post('/pro/complete-profile', [ProfileController::class, 'updateProProfile']);
});

// Client Setup
Route::post('/client/update-photo',    [ProfileController::class, 'updateClientPhoto']);

//Authenticated Routes (Token Required + Check Status)

Route::middleware(['auth:sanctum', 'check_status'])->group(function () {

    // General User Actions
    Route::post('/logout',     [AuthController::class, 'logout']);
    Route::delete('/account', [AuthController::class, 'deleteAccount']);
    Route::post('/contracts/{id}/report', [ReportController::class, 'store']);
    Route::post('/contracts/{id}/review',        [ReviewController::class, 'store']);


    //Client Routes

    Route::middleware('role:client')->group(function () {
        Route::get('/client/me', [ClientController::class, 'me']);

        // Job Management
        Route::post('/job-posts', [JobPostController::class, 'store']);
        Route::get('/jobs/{id}/applications', [ApplicationController::class, 'jobApplications']);
        Route::post('/applications/{id}/accept', [ApplicationController::class, 'accept']);
        Route::post('/applications/{id}/reject', [ApplicationController::class, 'reject']);

        // Contract Management
        Route::post('/contracts/{id}/confirm', [ContractController::class, 'confirm']);
        Route::post('/contracts/{id}/cancel', [ContractController::class, 'cancel']);

        // Subscriptions
        Route::post('/buy-plan/{id}', [SubscriptionController::class, 'buy']);

        Route::middleware(['auth:sanctum', 'role:client'])
            ->get('client/my-subscription', [SubscriptionController::class, 'mySubscription']);

        //Job Posts list
        Route::get('/client/job-posts', [ClientController::class, 'jobPosts']);

        // Contracts
        Route::get('/client/contracts/active', [ClientController::class, 'activeContracts']);
        Route::get('/client/contracts', [ClientController::class, 'allContracts']);

        // Applications
        Route::get('/client/applications', [ClientController::class, 'applications']);

        // Job Posts
        Route::get('/client/job-posts/count', [ClientController::class, 'jobPostCount']);
        Route::get('/client/job-posts/remaining', [ClientController::class, 'remainingJobPosts']);


        // Professionals
        Route::get('/professionals', [ProfessionalController::class, 'index']);
    });


    // Professional id Routes

    Route::middleware('role:professional')->group(function () {
        // Profile Management
        Route::post('/professional/profile', [ProfessionalController::class, 'updateProfile']);
        Route::get('/pro/me', [ProfessionalController::class, 'me']);

        // Job/Application Management
        Route::post('/jobs/{id}/apply', [ApplicationController::class, 'apply']);
        Route::get('/my-jobs', [ApplicationController::class, 'myAcceptedJobs']);
        Route::delete('/applications/{id}', [ApplicationController::class, 'withdraw']);
        Route::get('/matching-jobs', [JobPostController::class, 'getMatchingJobs']);

        // Work Completion (Both controllers used depending on your logic)
        Route::post('/jobs/{id}/complete', [JobPostController::class, 'complete']);
        Route::post('/contracts/{id}/complete', [ContractController::class, 'markCompleted']);

        // Contracts
        // Route::get('/my-contracts', [ContractController::class, 'myContracts']);
    });



    //Route for frontend

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/jobs', [ProfessionalController::class, 'jobs']);

        Route::post('/pro/apply', [ProfessionalController::class, 'apply']);

        Route::get('/pro/applications', [ProfessionalController::class, 'myApplications']);
        Route::post('/pro/withdraw', [ProfessionalController::class, 'withdraw']);
        Route::post('/pro/withdraw-application', [ProfessionalController::class, 'withdraw']);

        Route::get('/pro/contracts', [ProfessionalController::class, 'myContracts']);
        Route::post('/pro/complete', [ProfessionalController::class, 'completeContract']);
        Route::post('/pro/complete-contract', [ProfessionalController::class, 'completeContract']);

        Route::get('/pro/stats', [ProfessionalController::class, 'stats']);
    });






    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Professional Approval
        Route::post('/approve/{id}', [AdminController::class, 'approveProfessional']);
        Route::post('/reject/{id}', [AdminController::class, 'rejectProfessional']);

        // User Management
        Route::post('/suspend-user/{id}', [AdminController::class, 'suspendUser']);
        Route::post('/unsuspend-user/{id}', [AdminController::class, 'unsuspendUser']);

        // Report Management
        Route::get('/reports', [AdminController::class, 'reports']);
        Route::post('/reports/{id}/resolve', [AdminController::class, 'resolveReport']);

        // User Management
        Route::get('/users', [AdminController::class, 'users']);

        //jobs Management
        Route::get('/jobs', [AdminController::class, 'jobs']);

        //contracts Management
        Route::get('/contracts', [AdminController::class, 'contracts']);

        //forceCancelContract Management
        Route::post('/contracts/{id}/cancel', [AdminController::class, 'forceCancelContract']);

        //Plan Management (Admin CRUD)
        Route::post('/plans', [AdminController::class, 'createPlan']);
        Route::get('/plans', [AdminController::class, 'plans']);
        Route::put('/plans/{id}', [AdminController::class, 'updatePlan']);
        Route::delete('/plans/{id}', [AdminController::class, 'deletePlan']);

        //user delet managemnt
        Route::get('/deleted-users', [AdminController::class, 'deletedUsers']);
        Route::post('/restore-user/{id}', [AdminController::class, 'restoreUser']);
    });
});
