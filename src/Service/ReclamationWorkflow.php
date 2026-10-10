<?php

namespace App\Service;

use App\Entity\Reclamation;

/**
 * Règles de cycle de vie d'un ticket de réclamation.
 *
 *   ouvert ──► en_cours ──► resolu ──► ferme (définitif)
 *                ▲  │          │
 *                │  └─► ouvert │ (admin seulement)
 *                └─────────────┘ (réouverture)
 *
 * - « en cours » et « résolu » exigent un technicien assigné ;
 * - seul un administrateur peut remettre un ticket « ouvert » ou le « fermer » ;
 * - le passage à « résolu » enregistre la date de résolution, la réouverture l'efface.
 */
class ReclamationWorkflow
{
    public const ACTEUR_ADMIN = 'admin';
    public const ACTEUR_TECHNICIEN = 'technicien';

    public const LIBELLES = [
        Reclamation::ETAT_OUVERT => 'Ouvert',
        Reclamation::ETAT_EN_COURS => 'En cours',
        Reclamation::ETAT_RESOLU => 'Résolu',
        Reclamation::ETAT_FERME => 'Fermé',
    ];

    /** état de départ => [état d'arrivée => acteurs autorisés] */
    private const TRANSITIONS = [
        Reclamation::ETAT_OUVERT => [
            Reclamation::ETAT_EN_COURS => [self::ACTEUR_ADMIN, self::ACTEUR_TECHNICIEN],
        ],
        Reclamation::ETAT_EN_COURS => [
            Reclamation::ETAT_RESOLU => [self::ACTEUR_ADMIN, self::ACTEUR_TECHNICIEN],
            Reclamation::ETAT_OUVERT => [self::ACTEUR_ADMIN],
        ],
        Reclamation::ETAT_RESOLU => [
            Reclamation::ETAT_EN_COURS => [self::ACTEUR_ADMIN, self::ACTEUR_TECHNICIEN],
            Reclamation::ETAT_FERME => [self::ACTEUR_ADMIN],
        ],
        Reclamation::ETAT_FERME => [],
    ];

    private const ETATS_AVEC_TECHNICIEN = [Reclamation::ETAT_EN_COURS, Reclamation::ETAT_RESOLU];

    public function peutPasser(string $de, string $vers, string $acteur): bool
    {
        if ($de === $vers) {
            return true; // pas de changement d'état
        }

        return in_array($acteur, self::TRANSITIONS[$de][$vers] ?? [], true);
    }

    /**
     * @return string[] états accessibles depuis $de pour cet acteur (hors état actuel)
     */
    public function etatsAccessibles(string $de, string $acteur): array
    {
        $etats = [];
        foreach (self::TRANSITIONS[$de] ?? [] as $vers => $acteurs) {
            if (in_array($acteur, $acteurs, true)) {
                $etats[] = $vers;
            }
        }

        return $etats;
    }

    /**
     * Vérifie le changement demandé. Retourne un message d'erreur, ou null si tout est valide.
     */
    public function verifier(Reclamation $reclamation, string $ancienEtat, string $acteur): ?string
    {
        $nouvelEtat = (string) $reclamation->getEtatTicket();

        if (!$this->peutPasser($ancienEtat, $nouvelEtat, $acteur)) {
            return sprintf(
                'Transition non autorisée : « %s » → « %s ».',
                self::LIBELLES[$ancienEtat] ?? $ancienEtat,
                self::LIBELLES[$nouvelEtat] ?? $nouvelEtat
            );
        }

        if (in_array($nouvelEtat, self::ETATS_AVEC_TECHNICIEN, true) && null === $reclamation->getTechnicien()) {
            return sprintf(
                'Assignez un technicien avant de passer le ticket à l\'état « %s ».',
                self::LIBELLES[$nouvelEtat]
            );
        }

        return null;
    }

    /**
     * Applique les effets du changement d'état (date de résolution).
     */
    public function appliquer(Reclamation $reclamation, string $ancienEtat, ?\DateTime $maintenant = null): void
    {
        $nouvelEtat = $reclamation->getEtatTicket();

        if (Reclamation::ETAT_RESOLU === $nouvelEtat && Reclamation::ETAT_RESOLU !== $ancienEtat) {
            $reclamation->setDateResolution($maintenant ?? new \DateTime());
        }

        if (Reclamation::ETAT_RESOLU === $ancienEtat && Reclamation::ETAT_EN_COURS === $nouvelEtat) {
            $reclamation->setDateResolution(null); // réouverture
        }
    }
}
