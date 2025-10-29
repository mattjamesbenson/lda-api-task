<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Vacancy;

class ApprenticeshipApiService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('APPRENTICESHIP_API_KEY');
        $this->baseUrl = env('APPRENTICESHIP_API_URL');
    }

    public function fetchAndStore(): array
    {
        $response = Http::withToken($this->apiKey)
            ->get("{$this->baseUrl}/display-adverts", [
                'ukprn' => 10003035,
                'pageSize' => 50,
            ]);


        if (!$response->successful()) {
            return ['error' => 'Failed to fetch data'];
        }

        $data = $response->json()['value'] ?? [];

        $new = 0;
        $updated = 0;

        // Implement vacancy storage

        return ['new' => $new, 'updated' => $updated];
    }
}
