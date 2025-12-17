<?php

use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['message' => 'API aktif!']);
});

// News API
use App\Http\Controllers\NewsController;

Route::get('/news', [NewsController::class, 'indexApi']);
Route::post('/news', [NewsController::class, 'storeApi']);
Route::get('/news/{id}', [NewsController::class, 'showApi']);
Route::put('/news/{id}', [NewsController::class, 'updateApi']);
Route::delete('/news/{id}', [NewsController::class, 'destroyApi']);
// Additional API actions: view increment and like
Route::post('/news/{id}/view', [NewsController::class, 'incrementView']);
Route::post('/news/{id}/like', [NewsController::class, 'like']);
// Lightweight session notification endpoint (called by gateway after login)
Route::post('/session', [NewsController::class, 'sessionNotify']);
