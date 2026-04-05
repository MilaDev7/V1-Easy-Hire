<?php

use App\Http\Controllers\ChapaController;
use App\Models\Application;
use App\Models\Professional;
use App\Models\Report;
use App\Models\Review;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/search', function () {
    $location = trim(request('location', ''));
    $service = trim(request('service', ''));

    $query = Professional::with('user');

    if ($service !== '') {
        $query->where('skill', 'LIKE', '%'.$service.'%');
    }

    if ($location !== '') {
        $query->where('location', 'LIKE', '%'.$location.'%');
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

Route::get('/payment-success', [ChapaController::class, 'handlePaymentSuccess']);

Route::get('/payment-failed', [ChapaController::class, 'paymentFailed']);

Route::get('/professionals/{id}', function ($id) {
    $professional = Professional::with('user')->findOrFail($id);

    $completedJobs = Application::where('professional_id', $professional->user_id)
        ->where('status', 'accepted')
        ->whereHas('job', function ($query) {
            $query->where('status', 'completed');
        })
        ->with('job')
        ->get();

    $reviews = Review::where('reviewed_id', $professional->user_id)
        ->with('reviewer:id,name')
        ->orderBy('created_at', 'desc')
        ->get();

    $reportsCount = Report::where('reported_id', $professional->user_id)->count();

    return view('professional.show', [
        'professional' => $professional,
        'completedJobs' => $completedJobs,
        'reviews' => $reviews,
        'reportsCount' => $reportsCount,
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

Route::get('/pro/dashboard', function () {
    return view('professional.dashboard');
});

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
});
