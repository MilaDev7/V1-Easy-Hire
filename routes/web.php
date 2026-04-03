<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\JobController;
use App\Models\Application;
use App\Models\Professional;

Route::get('/', function () {
    return view('home');
});

Route::get('/search', function () {
    $location = trim(request('location', ''));
    $service = trim(request('service', ''));

    $query = Professional::with('user');

    if ($service !== '') {
        $query->where('skill', 'LIKE', '%' . $service . '%');
    }

    if ($location !== '') {
        $query->where('location', 'LIKE', '%' . $location . '%');
    }

    return view('search', [
        'professionals' => $query->get(),
        'searchLocation' => $location,
        'searchService' => $service,
    ]);
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/professionals/{id}', function ($id) {
    $professional = Professional::with('user')->findOrFail($id);

    $completedJobs = Application::where('professional_id', $professional->user_id)
        ->where('status', 'accepted')
        ->whereHas('job', function ($query) {
            $query->where('status', 'completed');
        })
        ->with('job')
        ->get();

    return view('professional.show', [
        'professional' => $professional,
        'completedJobs' => $completedJobs,
    ]);
});


// Step 2: Role-Specific Setup Forms
Route::get('/professional-setup', function () {
    return view('auth.pro-setup');
});
Route::get('/client-setup', function () {
    return view('auth.client-setup');
});



Route::get('/client/dashboard', function () {
    return view('client.dashboard');
});



