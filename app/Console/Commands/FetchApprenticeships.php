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
        $location = $this->argument('location');
        $radius = (float)$this->option('radius');

        // Convert postcode to longitude and latitude if provided
        $lat = $lon = null;

        if ($location) {
            $geo = Http::get("https://api.postcodes.io/postcodes/" . urlencode($location));

            if (!$geo->successful() || !$geo->json('result')) {
                $this->error("Invalid postcode: {$location}");
                return Command::FAILURE;
            }

            $lat = $geo->json('result.latitude');
            $lon = $geo->json('result.longitude');
        }

        $imported = $service->fetchAndStore();

        // If a postcode is provided, filter retrieved vacancies from the database by radius
        if ($location && $lat !== null && $lon !== null) {
            $count = 0;

            // Utilise ->cursor() instead of ->get() for potential future large scale database,
            // avoids storing large numbers of vacancies in memory
            foreach (DB::table('vacancies')->cursor() as $v) { 
                if (!$v->latitude || !$v->longitude) continue;

                if (haversineDistance($lat, $lon, $v->latitude, $v->longitude) <= $radius) {
                    $count++;
                    $this->line("- {$v->title} ({$v->employer_name}) [{$v->postcode}]");
                }
            }

            $this->info("Found {$count} vacancies within {$radius} km of {$location}");
        } else {
            $this->info('No postcode provided, skipping proximity filter.');
        }

        // Print summary of import
        $this->info("Imported {$imported['new']} new, updated {$imported['updated']}, total {$imported['total_in_db']} vacancies stored in the database.");

        // Print duplicates (if any)
        if (!empty($imported['duplicates'])) {
            $this->warn("Duplicates detected: {$imported['duplicates']}");
            if (!empty($imported['duplicate_refs'])) {
                $this->line("Duplicate references: " . implode(', ', $imported['duplicate_refs']));
            }
        }

        return Command::SUCCESS;
    }
}