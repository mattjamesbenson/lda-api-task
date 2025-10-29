<?php

namespace App\Http\Controllers;

use App\Services\ApprenticeshipApiService;
use Illuminate\Http\JsonResponse;

class DataController extends Controller
{
    public function ingest(ApprenticeshipApiService $service): JsonResponse
    {
        $result = $service->fetchAndStore();
        
        return response()->json([
            'status' => $result['error'] ?? 'success',
            'new_records' => $result['new'] ?? 0,
            'updated_records' => $result['updated'] ?? 0,
        ]);
    }
}
