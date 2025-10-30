<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ApprenticeshipApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FetchApprenticeships extends Command
{
    protected $signature = 'apprenticeship:fetch {location?} {--radius=10}';
    protected $description = 'Fetch apprenticeships from API and filter by UK postcode if value provided';

    public function handle(ApprenticeshipApiService $service)
    {
        
        $result = $service->fetchAndStore();
        $this->info("Imported {$result['new']} new, updated {$result['updated']}, total {$result['total_in_db']} vacancies stored in the database.");

        // Print duplicates if any
        if (!empty($result['duplicates'])) {
            $this->warn("Duplicates detected: {$result['duplicates']}");
            if (!empty($result['duplicate_refs'])) {
                $this->line("Duplicate references: " . implode(', ', $result['duplicate_refs']));
            }
        }
        
        $location = $this->argument('location');
        $radius = (float)$this->option('radius');

        if (!$location) {
            $this->info('No postcode provided, ignoring the proximity filter.');
            return Command::SUCCESS;
        }

        // Convert postcode to longitude and latitude
        $geo = Http::get("https://api.postcodes.io/postcodes/" . urlencode($location));

        if (!$geo->successful() || !$geo->json('result')) {
            $this->error("Invalid postcode: {$location}");
            return Command::FAILURE;
        }

        $lat = $geo->json('result.latitude');
        $lon = $geo->json('result.longitude');

        $vacancies = DB::table('vacancies')->get();
        
        // Filter results from database
        $results = $vacancies->filter(function ($v) use ($lat, $lon, $radius) {
            if (!$v->latitude || !$v->longitude) return false;
            return haversineDistance($lat, $lon, $v->latitude, $v->longitude) <= $radius;
        });

        // Log results in terminal
        $count = $results->count();
        $this->info("Found {$count} vacancies within {$radius} km of {$location}" . ($count > 0 ? ':' : ''));

        foreach ($results as $v) {
            $this->line("- {$v->title} ({$v->employer_name}) [{$v->postcode}]");
        }

        return Command::SUCCESS;
    }
}
