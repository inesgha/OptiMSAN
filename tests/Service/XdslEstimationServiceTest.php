<?php

namespace App\Tests\Service;

use App\Service\XdslEstimationService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'estimation du débit xDSL en fonction du standard et de la distance.
 */
class XdslEstimationServiceTest extends TestCase
{
    private XdslEstimationService $service;

    protected function setUp(): void
    {
        $this->service = new XdslEstimationService();
    }

    /**
     * À 0 km, on obtient le débit maximal théorique de chaque standard (partitions d'équivalence).
     */
    #[DataProvider('debitsMaximaux')]
    public function testDebitMaximalAZeroKm(string $standard, int $down, int $up): void
    {
        $debit = $this->service->estimerDebit($standard, 0);

        $this->assertSame(['down' => $down, 'up' => $up], $debit);
    }

    public static function debitsMaximaux(): array
    {
        return [
            'VDSL2' => ['VDSL2', 100000, 20000],
            'ADSL2+' => ['ADSL2+', 24000, 1000],
            'ADSL2' => ['ADSL2', 12000, 1000],
            'ADSL' => ['ADSL', 8000, 800],
            'standard inconnu → profil par défaut' => ['SHDSL', 10000, 1000],
        ];
    }

    public function testLeStandardNeDependPasDeLaCasseNiDesEspaces(): void
    {
        $this->assertSame(
            $this->service->estimerDebit('VDSL2', 1.2),
            $this->service->estimerDebit('  vdsl2 ', 1.2)
        );
    }

    public function testAdsl2PlusNestPasConfonduAvecAdsl2(): void
    {
        $adsl2Plus = $this->service->estimerDebit('ADSL2+', 0);
        $adsl2 = $this->service->estimerDebit('ADSL2', 0);

        $this->assertGreaterThan($adsl2['down'], $adsl2Plus['down']);
    }

    public function testLeDebitDiminueAvecLaDistance(): void
    {
        $proche = $this->service->estimerDebit('VDSL2', 0.5);
        $loin = $this->service->estimerDebit('VDSL2', 1.5);

        $this->assertGreaterThan($loin['down'], $proche['down']);
        $this->assertGreaterThan($loin['up'], $proche['up']);
    }

    public function testValeurCalculeeAUnKilometreEnVdsl2(): void
    {
        // 100000 × e^(-1/0.6) ≈ 18888 kbps ; 20000 × e^(-1/0.6) ≈ 3778 kbps
        $debit = $this->service->estimerDebit('VDSL2', 1);

        $this->assertSame(['down' => 18888, 'up' => 3778], $debit);
    }

    public function testLeDebitNeDescendJamaisSousLePlancher(): void
    {
        $debit = $this->service->estimerDebit('ADSL', 50);

        $this->assertSame(XdslEstimationService::DEBIT_MIN_DOWN, $debit['down']);
        $this->assertSame(XdslEstimationService::DEBIT_MIN_UP, $debit['up']);
    }

    public function testUneDistanceNegativeEstRefusee(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La distance ne peut pas être négative.');

        $this->service->estimerDebit('VDSL2', -0.01);
    }
}
