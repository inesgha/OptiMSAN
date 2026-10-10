<?php

namespace App\Service;

class GeoDistanceService
{
    private const RAYON_TERRE_KM = 6371;

    /**
     * Calcule la distance en km entre 2 points GPS (formule de Haversine), arrondie à 2 décimales.
     *
     * @throws \InvalidArgumentException si une latitude n'est pas dans [-90, 90]
     *                                   ou une longitude dans [-180, 180]
     */
    public function calculerDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $this->verifierCoordonnees($lat1, $lng1);
        $this->verifierCoordonnees($lat2, $lng2);

        $latRad1 = deg2rad($lat1);
        $latRad2 = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos($latRad1) * cos($latRad2) * sin($deltaLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round(self::RAYON_TERRE_KM * $c, 2);
    }

    private function verifierCoordonnees(float $lat, float $lng): void
    {
        if ($lat < -90 || $lat > 90) {
            throw new \InvalidArgumentException(sprintf('Latitude invalide : %s (attendu entre -90 et 90).', $lat));
        }
        if ($lng < -180 || $lng > 180) {
            throw new \InvalidArgumentException(sprintf('Longitude invalide : %s (attendu entre -180 et 180).', $lng));
        }
    }
}
