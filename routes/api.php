<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use App\Services\ApprenticeshipApiService;

// Data ingestion endpoint
Route::post('/ingest', function (ApprenticeshipApiService $service) {
    $result = $service->fetchAndStore();
    return response()->json($result);
});

// Proximity search endpoint
Route::get('/search', [SearchController::class, 'search']);
