<?php

use App\Http\Controllers\ChapaController;
use App\Http\Controllers\Api\AuthController;
use App\Models\Application;
use App\Models\Professional;
use App\Models\ProfessionalPortfolioItem;
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
})->name('login');

Route::post('/login', [AuthController::class, 'webLogin'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'webLogout'])->name('logout');

Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/payment-success', [ChapaController::class, 'handlePaymentSuccess']);

Route::get('/payment-failed', [ChapaController::class, 'paymentFailed']);

Route::get('/professional/{id}', function ($id) {
    $professional = Professional::with('user')->findOrFail($id);
    $reviewTargetIds = array_values(array_unique([
        (int) $professional->user_id,
        (int) $professional->id,
    ]));

    $completedJobs = Application::where('professional_id', $professional->user_id)
        ->where('status', 'accepted')
        ->whereHas('job', function ($query) {
            $query->where('status', 'completed');
        })
        ->with('job')
        ->get();

    $reviews = Review::whereIn('reviewed_id', $reviewTargetIds)
        ->with('reviewer:id,name')
        ->orderBy('created_at', 'desc')
        ->get();

    $reports = Report::whereIn('reported_id', $reviewTargetIds)
        ->with('reporter:id,name')
        ->orderBy('created_at', 'desc')
        ->get();

    $reportsCount = $reports->count();
    $averageRating = $reviews->count() > 0 ? round((float) $reviews->avg('rating'), 1) : 0;
    $portfolioItems = ProfessionalPortfolioItem::where('professional_id', $professional->id)
        ->latest()
        ->get();

    return view('professional.show', [
        'professional' => $professional,
        'completedJobs' => $completedJobs,
        'portfolioItems' => $portfolioItems,
        'reviews' => $reviews,
        'averageRating' => $averageRating,
        'reports' => $reports,
        'reportsCount' => $reportsCount,
    ]);
})->name('professional.show');

Route::get('/professionals/{id}', function ($id) {
    return redirect()->route('professional.show', ['id' => $id]);
});

// Step 2: Role-Specific Setup Forms
Route::get('/professional-setup', function () {
    return view('auth.pro-setup');
});
Route::get('/client-setup', function () {
    return view('auth.client-setup');
});

Route::middleware(['auth', 'check_status', 'role:client'])->group(function () {
    Route::get('/client/dashboard', function () {
        return view('client.dashboard');
    });
});

Route::middleware(['auth', 'check_status', 'role:professional'])->group(function () {
    Route::get('/pro/dashboard', function () {
        return view('professional.dashboard');
    });
});

Route::middleware(['auth', 'check_status', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    });
});
