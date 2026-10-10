<?php

namespace App\Tests\Service;

use App\Entity\Reclamation;
use App\Entity\Technicien;
use App\Service\ReclamationWorkflow;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires du cycle de vie d'un ticket (exigence EX-08, table de décision de la semaine 4).
 */
class ReclamationWorkflowTest extends TestCase
{
    private const ADMIN = ReclamationWorkflow::ACTEUR_ADMIN;
    private const TECH = ReclamationWorkflow::ACTEUR_TECHNICIEN;

    private ReclamationWorkflow $workflow;

    protected function setUp(): void
    {
        $this->workflow = new ReclamationWorkflow();
    }

    private function ticket(string $etat, bool $avecTechnicien = true): Reclamation
    {
        $ticket = (new Reclamation())->setEtatTicket($etat);
        if ($avecTechnicien) {
            $ticket->setTechnicien(new Technicien());
        }

        return $ticket;
    }

    /**
     * Toutes les combinaisons « état de départ → état d'arrivée » pour les deux acteurs.
     */
    #[DataProvider('transitions')]
    public function testMatriceDesTransitions(string $de, string $vers, bool $admin, bool $technicien): void
    {
        $this->assertSame($admin, $this->workflow->peutPasser($de, $vers, self::ADMIN), 'admin');
        $this->assertSame($technicien, $this->workflow->peutPasser($de, $vers, self::TECH), 'technicien');
    }

    public static function transitions(): array
    {
        //                                 de           vers         admin  technicien
        return [
            'ouvert → ouvert'          => ['ouvert',   'ouvert',   true,  true],
            'ouvert → en cours'        => ['ouvert',   'en_cours', true,  true],
            'ouvert → résolu'          => ['ouvert',   'resolu',   false, false],
            'ouvert → fermé'           => ['ouvert',   'ferme',    false, false],
            'en cours → ouvert'        => ['en_cours', 'ouvert',   true,  false],
            'en cours → résolu'        => ['en_cours', 'resolu',   true,  true],
            'en cours → fermé'         => ['en_cours', 'ferme',    false, false],
            'résolu → ouvert'          => ['resolu',   'ouvert',   false, false],
            'résolu → en cours'        => ['resolu',   'en_cours', true,  true],
            'résolu → fermé'           => ['resolu',   'ferme',    true,  false],
            'fermé → ouvert'           => ['ferme',    'ouvert',   false, false],
            'fermé → en cours'         => ['ferme',    'en_cours', false, false],
            'fermé → résolu'           => ['ferme',    'resolu',   false, false],
            'fermé → fermé'            => ['ferme',    'ferme',    true,  true],
        ];
    }

    public function testUnTicketFermeNaPlusAucuneTransition(): void
    {
        $this->assertSame([], $this->workflow->etatsAccessibles('ferme', self::ADMIN));
        $this->assertSame([], $this->workflow->etatsAccessibles('ferme', self::TECH));
    }

    // --- Table de décision (semaine 4) : C1 assigné (contrôlé dans le contrôleur), C2 transition autorisée, C3 vers « résolu »

    public function testR5TransitionInterditeVersUnAutreEtatQueResolu(): void
    {
        $ticket = $this->ticket('en_cours');
        $ticket->setEtatTicket('ouvert');

        $erreur = $this->workflow->verifier($ticket, 'en_cours', self::TECH);

        $this->assertSame('Transition non autorisée : « En cours » → « Ouvert ».', $erreur);
    }

    public function testR6TransitionInterditeVersResolu(): void
    {
        $ticket = $this->ticket('ouvert');
        $ticket->setEtatTicket('resolu');

        $erreur = $this->workflow->verifier($ticket, 'ouvert', self::TECH);

        $this->assertStringStartsWith('Transition non autorisée', (string) $erreur);
    }

    public function testR7ReouvertureEffaceLaDateDeResolution(): void
    {
        $ticket = $this->ticket('resolu')->setDateResolution(new \DateTime('2026-10-01'));
        $ticket->setEtatTicket('en_cours');

        $this->assertNull($this->workflow->verifier($ticket, 'resolu', self::TECH));
        $this->workflow->appliquer($ticket, 'resolu');

        $this->assertNull($ticket->getDateResolution());
    }

    public function testR8ResolutionEnregistreLaDate(): void
    {
        $maintenant = new \DateTime('2026-10-09 10:00');
        $ticket = $this->ticket('en_cours');
        $ticket->setEtatTicket('resolu');

        $this->assertNull($this->workflow->verifier($ticket, 'en_cours', self::TECH));
        $this->workflow->appliquer($ticket, 'en_cours', $maintenant);

        $this->assertSame($maintenant, $ticket->getDateResolution());
    }

    public function testEnCoursSansTechnicienEstRefuse(): void
    {
        $ticket = $this->ticket('ouvert', avecTechnicien: false);
        $ticket->setEtatTicket('en_cours');

        $erreur = $this->workflow->verifier($ticket, 'ouvert', self::ADMIN);

        $this->assertSame('Assignez un technicien avant de passer le ticket à l\'état « En cours ».', $erreur);
    }

    public function testAssignerUnTechnicienSansChangerLEtatEstAutorise(): void
    {
        $ticket = $this->ticket('ouvert');

        $this->assertNull($this->workflow->verifier($ticket, 'ouvert', self::ADMIN));
    }

    public function testResoluDejaResoluNeModifiePasLaDate(): void
    {
        $date = new \DateTime('2026-10-01');
        $ticket = $this->ticket('resolu')->setDateResolution($date);

        $this->workflow->appliquer($ticket, 'resolu', new \DateTime('2026-10-09'));

        $this->assertSame($date, $ticket->getDateResolution());
    }
}
