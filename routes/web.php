<?php

use App\Http\Controllers\GeminiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;

Route::get('/test-gemini', [GeminiController::class, 'test']);
Route::middleware('auth')->group(function () {
    Route::view('/', 'dashboard.index')
        ->name('dashboard');

    Route::get('/websites', [WebsiteController::class, 'index'])
        ->name('websites.index');

    Route::post('/websites', [WebsiteController::class, 'store'])
        ->name('websites.store');

    Route::post('/websites/{website}/analyze', [WebsiteController::class, 'analyze'])
        ->name('websites.analyze');
});

Route::prefix('admin')->name('admin.')->middleware(['fortify.context:admin', 'auth:admin'])->group(function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');
});

Route::get('/', function () {
    return view('welcome');
});
