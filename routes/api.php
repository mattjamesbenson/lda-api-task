<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataController;
use App\Http\Controllers\SearchController;

Route::post('/ingest', [DataController::class, 'ingest']);
Route::get('/search', [SearchController::class, 'search']);