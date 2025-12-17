<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthProxyController;
use App\Http\Controllers\NewsProxyController;
use Illuminate\Http\Request;

// Redirect GET /login (under api) to web login page to avoid MethodNotAllowed when visited by browser
Route::get('/login', function () {
	return redirect('/login');
});

Route::post('/register', [AuthProxyController::class, 'register']);
Route::post('/login', [AuthProxyController::class, 'login']);

// Proxy all /news and /news/* requests to news-service and forward Authorization header
Route::any('/news', [NewsProxyController::class, 'forward'])->middleware(\App\Http\Middleware\AttachAuthToken::class);
Route::any('/news/{path}', [NewsProxyController::class, 'forward'])
	->where('path', '.*')
	->middleware(\App\Http\Middleware\AttachAuthToken::class);
