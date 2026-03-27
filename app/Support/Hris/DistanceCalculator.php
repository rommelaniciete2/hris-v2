<?php

namespace App\Support\Hris;

class DistanceCalculator
{
    public function inMeters(
        float $latitudeFrom,
        float $longitudeFrom,
        float $latitudeTo,
        float $longitudeTo,
    ): int {
        $earthRadius = 6371000;

        $latitudeFromInRadians = deg2rad($latitudeFrom);
        $longitudeFromInRadians = deg2rad($longitudeFrom);
        $latitudeToInRadians = deg2rad($latitudeTo);
        $longitudeToInRadians = deg2rad($longitudeTo);

        $latitudeDelta = $latitudeToInRadians - $latitudeFromInRadians;
        $longitudeDelta = $longitudeToInRadians - $longitudeFromInRadians;

        $haversine = sin($latitudeDelta / 2) ** 2
            + cos($latitudeFromInRadians) * cos($latitudeToInRadians) * sin($longitudeDelta / 2) ** 2;

        $distance = 2 * $earthRadius * asin(min(1, sqrt($haversine)));

        return (int) round($distance);
    }
}
