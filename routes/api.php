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
use App\Http\Controllers\Api\ReportController;


// Public Routes (No Token Required)

Route::post('/register/client', [AuthController::class, 'registerClient']);
Route::post('/register/professional', [AuthController::class, 'registerProfessional']);
Route::post('/login', [AuthController::class, 'login']);

// Professionals are usually public so clients can browse them
Route::get('/professionals', [ProfessionalController::class, 'index']);
Route::get('/professionals/{id}', [ProfessionalController::class, 'show']);


//Authenticated Routes (Token Required + Check Status)

Route::middleware(['auth:sanctum', 'check_status'])->group(function () {
    
    // General User Actions
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/account', [AuthController::class, 'deleteAccount']);
    Route::post('/contracts/{id}/report', [ReportController::class, 'store']);
    Route::post('/contracts/{id}/review', [ReviewController::class, 'store']);

  
    //Client Routes

    Route::middleware('role:client')->group(function () {
        // Job Management
        Route::post('/job-posts', [JobPostController::class, 'store']);
        Route::get('/jobs/{id}/applications', [ApplicationController::class, 'jobApplications']);
        Route::post('/applications/{id}/accept', [ApplicationController::class, 'accept']);

        // Contract Management
        Route::post('/contracts/{id}/confirm', [ContractController::class, 'confirm']);
        Route::post('/contracts/{id}/cancel', [ContractController::class, 'cancel']);

        // Subscriptions
        Route::post('/buy-plan/{id}', [SubscriptionController::class, 'buy']);
    });


    // Professional Routes
   
    Route::middleware('role:professional')->group(function () {
        // Profile Management
        Route::post('/professional/profile', [ProfessionalController::class, 'updateProfile']);

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
    });
});