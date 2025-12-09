<?php

use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['message' => 'API aktif!']);
});

// News API
use App\Http\Controllers\NewsController;

Route::get('/news', [NewsController::class, 'list']);
Route::post('/news/{id}/like', [NewsController::class, 'like']);
Route::post('/news/{id}/view', [NewsController::class, 'incrementView']);

// Note: admin (web) resource routes moved to routes/web.php
