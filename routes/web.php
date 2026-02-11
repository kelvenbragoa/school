<?php

use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/api/translations/{locale}', [TranslationController::class, 'show']);
Route::post('/locale/change', [LocaleController::class, 'change'])->name('locale.change');

Route::get('/{any}', function () {
    return view('spa');
})->where('any', '.*');
