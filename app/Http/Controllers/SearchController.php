<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'postcode' => 'required|string',
            'radius' => 'required|numeric|min:1|max:100',
        ]);

        // Convert postcode to latitude/longitude using postcodes.io
        $geo = Http::get("https://api.postcodes.io/postcodes/" . urlencode($request->postcode));

        if (!$geo->successful() || !$geo->json()['result']) {
            return response()->json(['error' => 'Invalid postcode'], 400);
        }

        $lat = $geo->json()['result']['latitude'];
        $lon = $geo->json()['result']['longitude'];

        $radius = $request->radius;

        $results = DB::table('vacancies')->get()->filter(function($v) use ($lat, $lon, $radius) {
            return haversineDistance($lat, $lon, $v->latitude, $v->longitude) <= $radius;
        });

        return response()->json($results);
    }
}
