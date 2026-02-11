<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;

class TranslationController extends Controller
{
    public function show(string $locale): \Illuminate\Http\JsonResponse
    {
        if (! in_array($locale, ['pt', 'en'])) {
            return response()->json(['error' => 'Invalid locale'], 400);
        }

        App::setLocale($locale);

        return response()->json([
            'landing' => __('landing'),
        ]);
    }
}
