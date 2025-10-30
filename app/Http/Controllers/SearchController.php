<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PostcodeLookupService;

class SearchController extends Controller
{
    public function search(Request $request, PostcodeLookupService $postcodeService)
    {
        $request->validate([
            'postcode' => 'required|string',
            'radius' => 'required|numeric|min:1|max:100',
        ]);

        $coords = $postcodeService->lookup($request->postcode);
        $lat = $coords['latitude'];
        $lon = $coords['longitude'];
        $radius = $request->radius;

        $results = collect();

        // Select only necessary columns from the database and stream results with cursor() to avoid memory overload
        // when the vacancies table contains hundreds of thousands of rows (scaled for large datasets)
        foreach (DB::table('vacancies')->select('id', 'title', 'employer_name', 'postcode', 'latitude', 'longitude')->cursor() as $v) {
            if ($v->latitude !== null && $v->longitude !== null &&
                haversineDistance($lat, $lon, $v->latitude, $v->longitude) <= $radius
            ) {
                $results->push($v);
            }
        }

        return response()->json($results);
    }
}
