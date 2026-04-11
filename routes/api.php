<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\DirectRequestController;
use App\Http\Controllers\Api\JobPostController;
use App\Http\Controllers\Api\ProfessionalController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\ChapaController;
use Illuminate\Support\Facades\Route;

// Public Routes (No Token Required)

Route::post('/register/client', [AuthController::class, 'registerClient']);
Route::post('/register/professional', [AuthController::class, 'registerProfessional']);
Route::post('/login', [AuthController::class, 'login']);

// Public plans endpoint (no auth required)
Route::get('/plans', [AdminController::class, 'plans']);

// Chapa Payment Callback (public)
Route::get('/chapa/payment-success', [ChapaController::class, 'verifyPayment']);

// Professionals are usually public so clients can browse them
Route::get('/professionals', [ProfessionalController::class, 'index']);
Route::get('/professionals/{id}', [ProfessionalController::class, 'show']);

// Professional Setup
Route::middleware(['auth:sanctum', 'check_status', 'role:professional'])->group(function () {
    Route::get('/pro/profile', [ProfileController::class, 'getProProfile']);
    Route::post('/pro/profile-update', [ProfileController::class, 'updateProProfile']);
    Route::post('/pro/profile-update-simple', [ProfileController::class, 'updateProProfileSimple']);
    Route::post('/pro/complete-profile', [ProfileController::class, 'updateProProfile']);
    Route::get('/pro/portfolio', [ProfileController::class, 'myPortfolioItems']);
    Route::post('/pro/portfolio', [ProfileController::class, 'uploadPortfolioItem']);
    Route::delete('/pro/portfolio/{id}', [ProfileController::class, 'deletePortfolioItem']);
});

// Client Setup
Route::middleware(['auth:sanctum', 'check_status', 'role:client'])->group(function () {
    Route::post('/client/update-photo', [ProfileController::class, 'updateClientPhoto']);
    Route::get('/client/profile', [ProfileController::class, 'getClientProfile']);
    Route::post('/client/profile', [ProfileController::class, 'updateClientProfile']);
});

// Authenticated Routes (Token Required + Check Status)

Route::middleware(['auth:sanctum', 'check_status'])->group(function () {

    // General User Actions
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/account', [AuthController::class, 'deleteAccount']);
    Route::post('/contracts/{id}/report', [ReportController::class, 'store']);
    Route::post('/contracts/{id}/review', [ReviewController::class, 'store']);

    // Client Routes

    Route::middleware('role:client')->group(function () {
        Route::get('/client/me', [ClientController::class, 'me']);

        // Job Management
        Route::post('/job-posts', [JobPostController::class, 'store']);
        Route::delete('/job-posts/{id}', [ClientController::class, 'deleteJob']);
        Route::get('/jobs/{id}/applications', [ApplicationController::class, 'jobApplications']);
        Route::post('/applications/{id}/accept', [ApplicationController::class, 'accept']);
        Route::post('/applications/{id}/reject', [ApplicationController::class, 'reject']);

        // Contract Management
        Route::post('/contracts/{id}/confirm', [ContractController::class, 'confirm']);
        Route::post('/contracts/{id}/reject', [ContractController::class, 'reject']);
        Route::post('/contracts/{id}/cancel', [ContractController::class, 'cancel']);

        // Direct Requests
        Route::post('/send-request/{proId}', [DirectRequestController::class, 'sendRequest']);
        Route::get('/client/requests', [DirectRequestController::class, 'clientRequests']);
        Route::post('/client/requests/{id}/cancel', [DirectRequestController::class, 'cancelClientRequest']);
        Route::post('/client/requests/{id}/resend', [DirectRequestController::class, 'resendClientRequest']);

        // Subscriptions
        Route::post('/buy-plan/{id}', [SubscriptionController::class, 'buy']);

        // Chapa Payment
        Route::post('/chapa/initialize-payment', [ChapaController::class, 'initializePayment']);

        Route::get('client/my-subscription', [SubscriptionController::class, 'mySubscription']);

        // Job Posts list
        Route::get('/client/job-posts', [ClientController::class, 'jobPosts']);

        // Contracts
        Route::get('/client/contracts/active', [ClientController::class, 'activeContracts']);
        Route::get('/client/contracts', [ClientController::class, 'allContracts']);

        // Applications
        Route::get('/client/applications', [ClientController::class, 'applications']);

        // Job Posts
        Route::get('/client/job-posts/count', [ClientController::class, 'jobPostCount']);
        Route::get('/client/job-posts/remaining', [ClientController::class, 'remainingJobPosts']);
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

        // Direct Requests
        Route::get('/pro/requests', [DirectRequestController::class, 'myRequests']);
        Route::post('/pro/requests/{id}/accept', [DirectRequestController::class, 'acceptRequest']);
        Route::post('/pro/requests/{id}/reject', [DirectRequestController::class, 'rejectRequest']);
    });

    // Professional routes used by frontend
    Route::middleware('role:professional')->group(function () {
        Route::get('/jobs', [ProfessionalController::class, 'jobs']);

        Route::get('/pro/applications', [ProfessionalController::class, 'myApplications']);
        Route::post('/pro/withdraw', [ProfessionalController::class, 'withdraw']);

        Route::get('/pro/contracts', [ProfessionalController::class, 'myContracts']);
        Route::post('/pro/complete', [ProfessionalController::class, 'completeContract']);

        Route::get('/pro/stats', [ProfessionalController::class, 'stats']);
    });

    // Admin routes used by frontend
    Route::middleware('role:admin')->group(function () {
        // 🔥 PROFESSIONALS
        Route::get('/admin/professionals', [AdminController::class, 'allProfessionals']);
        Route::get('/admin/professionals/pending', [AdminController::class, 'pendingProfessionals']);
        Route::post('/admin/professionals/{id}/approve', [AdminController::class, 'approveProfessional']);
        Route::post('/admin/professionals/{id}/reject', [AdminController::class, 'rejectProfessional']);

        // 🔥 USERS
        Route::get('/admin/users', [AdminController::class, 'users']);
        Route::get('/admin/users/suspended', [AdminController::class, 'getSuspendedUsers']);
        Route::get('/admin/users/deleted', [AdminController::class, 'deletedUsers']);

        Route::post('/admin/users/{id}/suspend', [AdminController::class, 'suspendUser']);
        Route::post('/admin/users/{id}/unsuspend', [AdminController::class, 'unsuspendUser']);
        Route::post('/admin/users/{id}/restore', [AdminController::class, 'restoreUser']);

        // 🔥 JOBS & CONTRACTS
        Route::get('/admin/jobs', [AdminController::class, 'jobs']);
        Route::post('/admin/jobs/{id}/cancel', [AdminController::class, 'cancelJob']);
        Route::get('/admin/contracts', [AdminController::class, 'contracts']);
        Route::post('/admin/contracts/{id}/cancel', [AdminController::class, 'forceCancelContract']);

        // 🔥 REPORTS
        Route::get('/admin/reports', [AdminController::class, 'reports']);
        Route::post('/admin/reports/{id}/resolve', [AdminController::class, 'resolveReport']);

        // 🔥 PLANS
        Route::get('/admin/plans', [AdminController::class, 'plans']);
        Route::post('/admin/plans', [AdminController::class, 'createPlan']);
        Route::put('/admin/plans/{id}', [AdminController::class, 'updatePlan']);
        Route::delete('/admin/plans/{id}', [AdminController::class, 'deletePlan']);

        // 🔥 STATS
        Route::get('/admin/stats', [AdminController::class, 'stats']);
    });
});
