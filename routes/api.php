<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobPostController;
use App\Http\Controllers\Api\ApplicationController;

Route::post('/register/client', [AuthController::class, 'registerClient']);
Route::post('/register/professional', [AuthController::class, 'registerProfessional']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

//job post
Route::middleware(['auth:sanctum', 'role:client'])->group(function () {
    Route::post('/job-posts', [JobPostController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Professional routes
    Route::post('/jobs/{jobId}/apply', [ApplicationController::class, 'apply']);
    Route::get('/my-applications', [ApplicationController::class, 'myApplications']);

    // Client routes
    Route::get('/jobs/{jobId}/applications', [ApplicationController::class, 'jobApplications']);
    Route::patch('/applications/{applicationId}/status', [ApplicationController::class, 'updateStatus']);
});