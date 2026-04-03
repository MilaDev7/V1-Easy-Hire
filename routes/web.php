<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\JobController;

Route::get('/', function () {
    return view('home');
});

Route::get('/search', function () {
    return view('search');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/register', function () {
    return view('auth.register');
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







