<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobPostController;
use App\Http\Controllers\Api\ApplicationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);



Route::middleware('auth:sanctum')->group(function () {
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