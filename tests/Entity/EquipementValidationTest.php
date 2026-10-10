<?php

namespace App\Tests\Entity;

use App\Entity\PortMSAN;
use App\Entity\SiteMSAN;
use App\Entity\SlotCard;
use App\Entity\Zone;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Règles de validation de l'inventaire réseau (zones, sites, cartes, ports).
 */
class EquipementValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
    }

    private function nbErreurs(object $objet, string $champ): int
    {
        return count($this->validator->validateProperty($objet, $champ));
    }

    public function testCodePostalDeQuatreChiffres(): void
    {
        $this->assertSame(0, $this->nbErreurs((new Zone())->setCodePostal('1000'), 'codePostal'));
        $this->assertSame(1, $this->nbErreurs((new Zone())->setCodePostal('100'), 'codePostal'));
        $this->assertSame(1, $this->nbErreurs((new Zone())->setCodePostal('10000'), 'codePostal'));
    }

    public function testAdresseIpDuSite(): void
    {
        $this->assertSame(0, $this->nbErreurs((new SiteMSAN())->setAdresseIp('10.0.0.1'), 'adresseIp'));
        $this->assertSame(1, $this->nbErreurs((new SiteMSAN())->setAdresseIp('10.0.0.256'), 'adresseIp'));
    }

    public function testLeSiteDoitEtrePlaceSurLaCarte(): void
    {
        $this->assertSame(1, $this->nbErreurs(new SiteMSAN(), 'latitude'));
        $this->assertSame(1, $this->nbErreurs((new SiteMSAN())->setLatitude(90.5), 'latitude'));
        $this->assertSame(0, $this->nbErreurs((new SiteMSAN())->setLatitude(36.8), 'latitude'));
    }

    public function testNombreDePortsDUneCarteEntre1Et128(): void
    {
        $this->assertSame(1, $this->nbErreurs((new SlotCard())->setNombrePortsTotal(0), 'nombrePortsTotal'));
        $this->assertSame(0, $this->nbErreurs((new SlotCard())->setNombrePortsTotal(1), 'nombrePortsTotal'));
        $this->assertSame(0, $this->nbErreurs((new SlotCard())->setNombrePortsTotal(128), 'nombrePortsTotal'));
        $this->assertSame(1, $this->nbErreurs((new SlotCard())->setNombrePortsTotal(129), 'nombrePortsTotal'));
    }

    public function testEtatDuPort(): void
    {
        foreach (PortMSAN::ETATS as $etat) {
            $this->assertSame(0, $this->nbErreurs((new PortMSAN())->setEtatPort($etat), 'etatPort'), $etat);
        }
        $this->assertSame(1, $this->nbErreurs((new PortMSAN())->setEtatPort('casse'), 'etatPort'));
    }

    public function testNumeroDePortNegatifRefuse(): void
    {
        $this->assertSame(0, $this->nbErreurs((new PortMSAN())->setNumeroPort(0), 'numeroPort'));
        $this->assertSame(1, $this->nbErreurs((new PortMSAN())->setNumeroPort(-1), 'numeroPort'));
    }
}
