<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ApprenticeshipApiService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected int $ukprn;

    public function __construct()
    {
        $this->apiKey  = config('services.apprenticeships.key');
        $this->baseUrl = rtrim(config('services.apprenticeships.url'), '/');
        $this->ukprn   = config('services.apprenticeships.ukprn'); // read from config

        if (!$this->apiKey || !$this->baseUrl || !$this->ukprn) {
            throw new RuntimeException('Apprenticeship API key, URL, or UKPRN missing.');
        }
    }

    public function fetchAndStore(int $pageSize = 50, int $pageNumber = 1): array
    {
        $params = [
            'Ukprn' => $this->ukprn,
            'FilterBySubscription' => 'true',
            'PageSize' => $pageSize,
            'PageNumber' => $pageNumber
        ];

        $url = $this->baseUrl . '/vacancies/vacancy';

        // Log to check URL structure is correct
        Log::info('Calling Apprenticeship API', ['url' => $url, 'params' => $params]);

        // Make API call
        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $this->apiKey,
            'X-Version' => '2',
            'Accept' => 'application/json; ver=2'
        ])->get($url, $params);

        // Handle unsuccessful responses
        if (!$response->successful()) {
            throw new RuntimeException("API call failed with status {$response->status()}");
        }

        // Convert response to JSON
        $json = $response->json();

        // Error handling for no vacancies returned in the response
        if (!isset($json['vacancies']) || !is_array($json['vacancies'])) {
            Log::error('API response missing "vacancies" array', ['json' => $json]);
            throw new RuntimeException('No vacancies returned by API.');
        }

        $new = 0;
        $updated = 0;
        $duplicates = 0;
        $seenRefs = [];
        $duplicateRefs = [];

        // Loop all vacancies and create/update
        foreach ($json['vacancies'] as $item) {
            try {
                $ref = $item['vacancyReference'] ?? null;

                // Handle items where no reference found
                if (!$ref) {
                    $duplicates++;
                    continue;
                }

                // Track duplicates in API payload only
                if (in_array($ref, $seenRefs)) {
                    $duplicates++;
                    $duplicateRefs[] = $ref;
                    continue;
                }

                $seenRefs[] = $ref;

                $postcode = $item['addresses'][0]['postcode'] ?? null;
                $lat = $item['addresses'][0]['latitude'] ?? null;
                $lon = $item['addresses'][0]['longitude'] ?? null;

                $vacancy = Vacancy::updateOrCreate(
                    ['vacancy_reference' => $ref],
                    [
                        'title' => $item['title'] ?? '',
                        'employer_name' => $item['employerName'] ?? '',
                        'postcode' => $postcode,
                        'latitude' => $lat,
                        'longitude' => $lon,
                        'description' => $item['description'] ?? '',
                    ]
                );

                $vacancy->wasRecentlyCreated ? $new++ : $updated++;
            } catch (\Throwable $e) {
                Log::error('Failed to import vacancy', ['vacancy' => $item, 'error' => $e->getMessage()]);
            }
        }

        $totalInDb = Vacancy::count();

        // Log successful import
        Log::info('Import finished', [
            'new' => $new,
            'updated' => $updated,
            'duplicates' => $duplicates,
            'duplicate_refs' => $duplicateRefs,
            'total_in_db' => $totalInDb
        ]);

        return [
            'new' => $new,
            'updated' => $updated,
            'duplicates' => $duplicates,
            'duplicate_refs' => $duplicateRefs,
            'total_in_db' => $totalInDb
        ];
    }
}
