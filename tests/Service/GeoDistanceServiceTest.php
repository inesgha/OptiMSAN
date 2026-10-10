<?php

namespace App\Tests\Service;

use App\Service\GeoDistanceService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires du calcul de distance GPS (formule de Haversine).
 */
class GeoDistanceServiceTest extends TestCase
{
    private GeoDistanceService $service;

    protected function setUp(): void
    {
        // Arrange commun : le service n'a aucune dépendance
        $this->service = new GeoDistanceService();
    }

    public function testDistanceEntreUnPointEtLuiMemeEstNulle(): void
    {
        $distance = $this->service->calculerDistanceKm(36.8065, 10.1815, 36.8065, 10.1815);

        $this->assertSame(0.0, $distance);
    }

    public function testUnDegreDeLatitudeVautEnviron111Km(): void
    {
        $distance = $this->service->calculerDistanceKm(0, 0, 1, 0);

        $this->assertEqualsWithDelta(111.19, $distance, 0.01);
    }

    public function testDistanceTunisSfax(): void
    {
        // Tunis (36.8065, 10.1815) → Sfax (34.7406, 10.7603) : environ 235 km à vol d'oiseau
        $distance = $this->service->calculerDistanceKm(36.8065, 10.1815, 34.7406, 10.7603);

        $this->assertEqualsWithDelta(235, $distance, 3);
    }

    public function testLaDistanceEstSymetrique(): void
    {
        $aller = $this->service->calculerDistanceKm(36.8065, 10.1815, 35.8256, 10.6084);
        $retour = $this->service->calculerDistanceKm(35.8256, 10.6084, 36.8065, 10.1815);

        $this->assertSame($aller, $retour);
    }

    public function testLeResultatEstArrondiADeuxDecimales(): void
    {
        $distance = $this->service->calculerDistanceKm(36.8065, 10.1815, 36.8123, 10.1901);

        $this->assertSame(round($distance, 2), $distance);
    }

    /**
     * Valeurs limites des coordonnées : sur la limite = accepté.
     */
    #[DataProvider('coordonneesLimitesValides')]
    public function testCoordonneesSurLesLimitesSontAcceptees(float $lat, float $lng): void
    {
        $distance = $this->service->calculerDistanceKm($lat, $lng, 0, 0);

        $this->assertGreaterThanOrEqual(0, $distance);
    }

    public static function coordonneesLimitesValides(): array
    {
        return [
            'latitude 90' => [90, 0],
            'latitude -90' => [-90, 0],
            'longitude 180' => [0, 180],
            'longitude -180' => [0, -180],
        ];
    }

    /**
     * Valeurs limites des coordonnées : juste au-delà = exception.
     */
    #[DataProvider('coordonneesInvalides')]
    public function testCoordonneesHorsLimitesLevenUneException(float $lat, float $lng): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->calculerDistanceKm($lat, $lng, 0, 0);
    }

    public static function coordonneesInvalides(): array
    {
        return [
            'latitude 90.0001' => [90.0001, 0],
            'latitude -90.0001' => [-90.0001, 0],
            'longitude 180.0001' => [0, 180.0001],
            'longitude -180.0001' => [0, -180.0001],
        ];
    }
}
