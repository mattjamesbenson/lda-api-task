<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\ApprenticeshipApiService;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApprenticeshipApiServiceTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fetches_and_stores_new_vacancies():void
    {
        // Fake an API response
        Http::fake([
            '*' => Http::response([
                'vacancies' => [
                    [
                        'vacancyReference' => 'VACANCY1',
                        'title' => 'Test Apprentice 1',
                        'employerName' => 'Employer 1',
                        'description' => 'Job Description 1',
                        'addresses' => [
                            [
                                'postcode' => 'M28 1NE',
                                'latitude' => 53.5071,
                                'longitude' => -2.3543,   
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $service = app(ApprenticeshipApiService::class);

        $result = $service->fetchAndStore();

        $this->assertEquals(1, $result['new']);
        $this->assertEquals(0, $result['updated']);

        $this->assertDatabaseHas('vacancies', [
            'vacancy_reference' => 'VACANCY1',
            'title' => 'Test Apprentice 1',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_existing_vacancy():void
    {
        // Create a test vacancy
        Vacancy::create([
            'vacancy_reference' => 'VACANCY1',
            'title' => 'Old Title',
            'employer_name' => 'Old Name',
            'description' => 'Old Desc',
            'postcode' => 'M28 1NE',
            'latitude' => 53.5071,
            'longitude' => -2.3543,
        ]);

        Http::fake([
            '*' => Http::response([
                'vacancies' => [
                    [
                        'vacancyReference' => 'VACANCY1',
                        'title' => 'New Title',
                        'employerName' => 'New Name',
                        'description' => 'New Desc',
                        'addresses' => [
                            [
                                'postcode' => 'M28 1NE',
                                'latitude' => 53.5071,
                                'longitude' => -2.3543,
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $service = app(ApprenticeshipApiService::class);
        $result = $service->fetchAndStore();

        $this->assertEquals(0, $result['new']);
        $this->assertEquals(1, $result['updated']);

        $this->assertDatabaseHas('vacancies', [
            'vacancy_reference' => 'VACANCY1',
            'title' => 'New Title',
            'employer_name' => 'New Name',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_empty_vacancies_response():void
    {
        Http::fake([
            '*' => Http::response(['vacancies' => []], 200),
        ]);

        $service = app(ApprenticeshipApiService::class);
        $result = $service->fetchAndStore();

        $this->assertEquals(0, $result['new']);
        $this->assertEquals(0, $result['updated']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_on_failed_api():void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $service = app(ApprenticeshipApiService::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API call failed with status 500');

        $service->fetchAndStore();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_detects_duplicates_in_api_payload(): void
    {
        Http::fake([
            '*' => Http::response([
                'vacancies' => [
                    [
                        'vacancyReference' => 'VACANCY2',
                        'title' => 'Test Apprentice 2',
                        'employerName' => 'Employer 2',
                        'description' => 'Job Description 2',
                        'addresses' => [['postcode' => 'M28 1NE', 'latitude' => 53.5071, 'longitude' => -2.3543]],
                    ],
                    [
                        'vacancyReference' => 'VACANCY2',
                        'title' => 'Test Apprentice 2 Duplicate',
                        'employerName' => 'Employer 2',
                        'description' => 'Job Description 2',
                        'addresses' => [['postcode' => 'M28 1NE', 'latitude' => 53.5071, 'longitude' => -2.3543]],
                    ]
                ]
            ], 200)
        ]);

        $service = app(ApprenticeshipApiService::class);
        $result = $service->fetchAndStore();

        $this->assertEquals(1, $result['new']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(1, $result['duplicates']);
        $this->assertContains('VACANCY2', $result['duplicate_refs']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_skips_vacancies_with_no_reference(): void
    {
        Http::fake([
            '*' => Http::response([
                'vacancies' => [
                    [
                        'vacancyReference' => null,
                        'title' => 'Test Apprentice 3',
                        'employerName' => 'Employer 3',
                        'description' => 'Job Description 3',
                        'addresses' => [['postcode' => 'M28 1NE', 'latitude' => 53.5071, 'longitude' => -2.3543]],
                    ]
                ]
            ], 200)
        ]);

        $service = app(ApprenticeshipApiService::class);
        $result = $service->fetchAndStore();

        $this->assertEquals(0, $result['new']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(0, count($result['duplicate_refs']));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_existing_database_vacancy(): void
    {
        Vacancy::create([
            'vacancy_reference' => 'VACANCY4',
            'title' => 'Old',
            'employer_name' => 'Employer 4',
            'description' => 'Job Description 4',
            'postcode' => 'M28 1NE',
            'latitude' => 53.5071,
            'longitude' => -2.3543,
        ]);

        Http::fake([
            '*' => Http::response([
                'vacancies' => [
                    [
                        'vacancyReference' => 'VACANCY4',
                        'title' => 'Old Updated',
                        'employerName' => 'Employer 4',
                        'description' => 'Job Description 4',
                        'addresses' => [['postcode' => 'M28 1NE', 'latitude' => 53.5071, 'longitude' => -2.3543]],
                    ]
                ]
            ], 200)
        ]);

        $service = app(ApprenticeshipApiService::class);
        $result = $service->fetchAndStore();

        $this->assertEquals(0, $result['new']);
        $this->assertEquals(1, $result['updated']);
        $this->assertEquals(0, $result['duplicates']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_if_vacancies_key_missing(): void
    {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        $service = app(ApprenticeshipApiService::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No vacancies returned by API');

        $service->fetchAndStore();
    }
}
