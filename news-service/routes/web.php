<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

use App\Http\Controllers\NewsController;

// Public CRUD routes for News (dashboard + CRUD). Named under 'admin.' to match existing Blade route() calls.
Route::resource('news', NewsController::class, ['as' => 'admin']);

// Placeholder image endpoint
Route::get('/news/{id}/placeholder', [NewsController::class, 'placeholder'])->name('news.placeholder');
