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


//registertion user

Route::post('/register/client', [AuthController::class, 'registerClient']);
Route::post('/register/professional', [AuthController::class, 'registerProfessional']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

//job post
Route::middleware(['auth:sanctum', 'role:client'])->group(function () {
    Route::post('/job-posts', [JobPostController::class, 'store']);
});


// Professional applies
Route::middleware(['auth:sanctum', 'role:professional'])->group(function () {
    Route::post('/jobs/{id}/apply', [ApplicationController::class, 'apply']);
});

// Client views applications
Route::middleware(['auth:sanctum', 'role:client'])->group(function () {
    Route::get('/jobs/{id}/applications', [ApplicationController::class, 'jobApplications']);

Route::middleware(['auth:sanctum', 'role:client'])->post('/applications/{id}/accept', [ApplicationController::class, 'accept']);
});
// Professional see applied appliction
Route::middleware(['auth:sanctum', 'role:professional'])
    ->get('/my-jobs', [ApplicationController::class, 'myAcceptedJobs']);

// Professional complet status

Route::middleware(['auth:sanctum', 'role:professional'])
    ->post('/jobs/{id}/complete', [JobPostController::class, 'complete']);

   //ProfessionalController
Route::middleware(['auth:sanctum', 'role:professional'])
    ->post('/professional/profile', [ProfessionalController::class, 'updateProfile']);
//clent get pro profile
    Route::get('/professionals', [ProfessionalController::class, 'index']);
//show pro
    Route::get('/professionals/{id}', [ProfessionalController::class, 'show']);

  
//admin controll
Route::post('/admin/approve/{id}', [AdminController::class, 'approveProfessional']);
Route::post('/admin/reject/{id}', [AdminController::class, 'rejectProfessional']); 
//admin
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/admin/approve/{id}', [AdminController::class, 'approveProfessional']);
    Route::post('/admin/reject/{id}', [AdminController::class, 'rejectProfessional']);
});

// Professional marks complete
Route::middleware(['auth:sanctum', 'role:professional'])
    ->post('/contracts/{id}/complete', [ContractController::class, 'markCompleted']);

// Client confirms
Route::middleware(['auth:sanctum', 'role:client'])
    ->post('/contracts/{id}/confirm', [ContractController::class, 'confirm']);

    //client cancel contract
Route::middleware(['auth:sanctum', 'role:client'])
    ->post('/contracts/{id}/cancel', [ContractController::class, 'cancel']);

//review
Route::middleware(['auth:sanctum'])->post(
    '/contracts/{id}/review',
    [ReviewController::class, 'store']
);


//SubscriptionController
Route::middleware(['auth:sanctum', 'role:client'])
    ->post('/buy-plan/{id}', [SubscriptionController::class, 'buy']);

    //matching-jobs
    Route::middleware(['auth:sanctum', 'role:professional'])
    ->get('/matching-jobs', [JobPostController::class, 'getMatchingJobs']);

    //withdraal


    Route::middleware(['auth:sanctum', 'role:professional'])
    ->delete('/applications/{id}', [ApplicationController::class, 'withdraw']);

    //Report

    Route::middleware(['auth:sanctum'])
    ->post('/contracts/{id}/report', [ReportController::class, 'store']);

    //delete account 
    Route::middleware('auth:sanctum')
    ->delete('/account', [AuthController::class, 'deleteAccount']);