<?php

use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

// Translation API (public)
Route::get('/api/translations/{locale}', [TranslationController::class, 'show']);

// Locale change
Route::post('/locale/change', [LocaleController::class, 'change'])->name('locale.change');

// SPA Catch-all route (must be last)
Route::get('/{any}', function () {
    return view('spa');
})->where('any', '.*');
