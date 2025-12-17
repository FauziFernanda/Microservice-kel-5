<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // If the user is already logged in (has access_token), send them to the news index
    if (session('access_token')) {
        return redirect('/news');
    }

    return view('welcome');
});

// API routes are loaded by the application's RouteServiceProvider.

use App\Http\Controllers\NewsProxyController;

// Serve a simple login form from the gateway so browsers can authenticate via gateway
Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/register', function () {
    return view('auth.register');
});

// Accept form submissions under web middleware so session state persists
Route::post('/register', [\App\Http\Controllers\AuthProxyController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthProxyController::class, 'login']);

// Logout (clear gateway session token)
use Illuminate\Http\Request;
Route::post('/logout', function (Request $request) {
    $request->session()->forget('access_token');
    // After logout we redirect to the login page per UX requirement
    return redirect('/login');
});

// Web proxy for news pages (so browser can visit /news/* and have token forwarded)
// Dashboard: render HTML using server-side fetch
Route::get('/news/dashboard', [NewsProxyController::class, 'dashboard']);

// Serve the gateway's News Management page (fetches API and renders cards)
Route::get('/news', [NewsProxyController::class, 'dashboard']);

// Proxy fallback for other /news pages (API / JSON or arbitrary paths)
Route::get('/news/{path}', [NewsProxyController::class, 'forward'])
    ->where('path', '.*')
    ->middleware(\App\Http\Middleware\AttachAuthToken::class);
