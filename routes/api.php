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


// Professional applies
Route::middleware(['auth:sanctum', 'role:professional'])->group(function () {
    Route::post('/jobs/{id}/apply', [ApplicationController::class, 'apply']);
});

// Client views applications
Route::middleware(['auth:sanctum', 'role:client'])->group(function () {
    Route::get('/jobs/{id}/applications', [ApplicationController::class, 'jobApplications']);

Route::middleware(['auth:sanctum', 'role:client'])->post('/applications/{id}/accept', [ApplicationController::class, 'accept']);
});




