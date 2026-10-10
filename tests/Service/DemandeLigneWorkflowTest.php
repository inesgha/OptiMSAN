<?php

namespace App\Tests\Service;

use App\Entity\DemandeLigne;
use App\Entity\PortMSAN;
use App\Service\DemandeLigneWorkflow;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires du cycle de vie d'une demande de ligne et de l'affectation des ports (exigence EX-09).
 */
class DemandeLigneWorkflowTest extends TestCase
{
    private DemandeLigneWorkflow $workflow;

    protected function setUp(): void
    {
        $this->workflow = new DemandeLigneWorkflow();
    }

    private function port(int $numero, string $etat = PortMSAN::ETAT_LIBRE): PortMSAN
    {
        return (new PortMSAN())->setNumeroPort($numero)->setEtatPort($etat);
    }

    private function demande(string $etat, ?PortMSAN $port = null): DemandeLigne
    {
        return (new DemandeLigne())->setEtatDemande($etat)->setPortMSAN($port);
    }

    #[DataProvider('transitions')]
    public function testMatriceDesTransitions(string $de, string $vers, bool $attendu): void
    {
        $this->assertSame($attendu, $this->workflow->peutPasser($de, $vers));
    }

    public static function transitions(): array
    {
        return [
            'en attente → validée' => ['en_attente', 'validee', true],
            'en attente → refusée' => ['en_attente', 'refusee', true],
            'en attente → en cours' => ['en_attente', 'en_cours', false],
            'validée → en cours' => ['validee', 'en_cours', true],
            'validée → refusée' => ['validee', 'refusee', true],
            'validée → installée' => ['validee', 'installee', false],
            'en cours → installée' => ['en_cours', 'installee', true],
            'en cours → refusée' => ['en_cours', 'refusee', false],
            'installée → en cours' => ['installee', 'en_cours', false],
            'refusée → validée' => ['refusee', 'validee', false],
        ];
    }

    public function testAffecterUnPortLibreLeRendOccupe(): void
    {
        $port = $this->port(3);
        $demande = $this->demande('validee', $port);

        $this->assertNull($this->workflow->verifier($demande, 'validee', null));
        $this->workflow->appliquer($demande, null, new \DateTime('2026-10-09'));

        $this->assertSame(PortMSAN::ETAT_OCCUPE, $port->getEtatPort());
        $this->assertEquals(new \DateTime('2026-10-09'), $port->getDateAffectation());
    }

    #[DataProvider('portsNonLibres')]
    public function testAffecterUnPortNonLibreEstRefuse(string $etat): void
    {
        $demande = $this->demande('validee', $this->port(3, $etat));

        $erreur = $this->workflow->verifier($demande, 'validee', null);

        $this->assertSame(sprintf('Le port n°3 n\'est pas libre (état : %s).', $etat), $erreur);
    }

    public static function portsNonLibres(): array
    {
        return [['occupe'], ['panne'], ['reserve']];
    }

    public function testGarderLeMemePortNeDeclenchePasLeControleDeDisponibilite(): void
    {
        $port = $this->port(3, PortMSAN::ETAT_OCCUPE);
        $demande = $this->demande('en_cours', $port);

        $this->assertNull($this->workflow->verifier($demande, 'validee', $port));
    }

    public function testEnCoursDInstallationSansPortEstRefuse(): void
    {
        $demande = $this->demande('en_cours');

        $erreur = $this->workflow->verifier($demande, 'validee', null);

        $this->assertSame('Affectez un port avant de passer la demande à l\'état « En cours d\'installation ».', $erreur);
    }

    public function testChangerDePortLibereLAncien(): void
    {
        $ancien = $this->port(3, PortMSAN::ETAT_OCCUPE);
        $nouveau = $this->port(4);
        $demande = $this->demande('validee', $nouveau);

        $this->assertNull($this->workflow->verifier($demande, 'validee', $ancien));
        $this->workflow->appliquer($demande, $ancien);

        $this->assertSame(PortMSAN::ETAT_LIBRE, $ancien->getEtatPort());
        $this->assertSame(PortMSAN::ETAT_OCCUPE, $nouveau->getEtatPort());
    }

    public function testRefuserUneDemandeLibereSonPort(): void
    {
        $port = $this->port(3, PortMSAN::ETAT_OCCUPE);
        $demande = $this->demande('refusee', $port);

        $this->assertNull($this->workflow->verifier($demande, 'validee', $port));
        $this->workflow->appliquer($demande, $port);

        $this->assertSame(PortMSAN::ETAT_LIBRE, $port->getEtatPort());
        $this->assertNull($demande->getPortMSAN());
    }

    public function testUneDemandeClotureeNePeutPlusEtreModifiee(): void
    {
        $demande = $this->demande('validee');

        $erreur = $this->workflow->verifier($demande, 'refusee', null);

        $this->assertSame('Cette demande est clôturée (« Refusée ») : elle ne peut plus être modifiée.', $erreur);
    }

    public function testUnPortEnPanneDejaAffecteGardeSonEtat(): void
    {
        $port = $this->port(3, PortMSAN::ETAT_PANNE);
        $demande = $this->demande('installee', $port);

        $this->workflow->appliquer($demande, $port);

        $this->assertSame(PortMSAN::ETAT_PANNE, $port->getEtatPort());
    }
}
