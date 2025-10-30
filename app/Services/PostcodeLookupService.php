<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PostcodeLookupService
{
    public function lookup(string $postcode): array
    {
        $response = Http::get("https://api.postcodes.io/postcodes/" . urlencode($postcode));

        if (!$response->successful() || !isset($response['result'])) {
            throw new RuntimeException("Invalid postcode: {$postcode}");
        }

        // Return converted longitude and latitude from postcode value
        return [
            'latitude' => $response->json('result.latitude'),
            'longitude' => $response->json('result.longitude'),
        ];
    }
}
