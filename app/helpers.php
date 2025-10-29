<?php

// Haversine formula calculates the distance between two points
// on a sphereical object (Earth) using incoming latitude and longitudes
if (!function_exists('haversineDistance')) {
    function haversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371; // km
        return $earthRadius * acos(
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            cos(deg2rad($lon2) - deg2rad($lon1)) +
            sin(deg2rad($lat1)) * sin(deg2rad($lat2))
        );
    }
}
