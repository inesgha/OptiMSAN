<?php

namespace App\Service;

use App\Entity\DemandeLigne;
use App\Entity\PortMSAN;

/**
 * Règles de cycle de vie d'une demande de ligne et d'affectation des ports.
 *
 *   en_attente ──► validee ──► en_cours ──► installee (définitif)
 *        │            │
 *        └────────────┴──► refusee (définitif)
 *
 * - « en cours d'installation » et « installée » exigent un port affecté ;
 * - seul un port « libre » peut être affecté ; il passe alors à « occupé » ;
 * - un port retiré ou appartenant à une demande refusée redevient « libre ».
 */
class DemandeLigneWorkflow
{
    public const LIBELLES = [
        DemandeLigne::ETAT_EN_ATTENTE => 'En attente',
        DemandeLigne::ETAT_VALIDEE => 'Validée',
        DemandeLigne::ETAT_EN_COURS => 'En cours d\'installation',
        DemandeLigne::ETAT_INSTALLEE => 'Installée',
        DemandeLigne::ETAT_REFUSEE => 'Refusée',
    ];

    private const TRANSITIONS = [
        DemandeLigne::ETAT_EN_ATTENTE => [DemandeLigne::ETAT_VALIDEE, DemandeLigne::ETAT_REFUSEE],
        DemandeLigne::ETAT_VALIDEE => [DemandeLigne::ETAT_EN_COURS, DemandeLigne::ETAT_REFUSEE],
        DemandeLigne::ETAT_EN_COURS => [DemandeLigne::ETAT_INSTALLEE],
        DemandeLigne::ETAT_INSTALLEE => [],
        DemandeLigne::ETAT_REFUSEE => [],
    ];

    private const ETATS_AVEC_PORT = [DemandeLigne::ETAT_EN_COURS, DemandeLigne::ETAT_INSTALLEE];

    public function peutPasser(string $de, string $vers): bool
    {
        return $de === $vers || in_array($vers, self::TRANSITIONS[$de] ?? [], true);
    }

    public function estCloturee(string $etat): bool
    {
        return [] === (self::TRANSITIONS[$etat] ?? []);
    }

    /**
     * @return string[]
     */
    public function etatsAccessibles(string $de): array
    {
        return self::TRANSITIONS[$de] ?? [];
    }

    /**
     * Vérifie la modification demandée. Retourne un message d'erreur, ou null si tout est valide.
     */
    public function verifier(DemandeLigne $demande, string $ancienEtat, ?PortMSAN $ancienPort): ?string
    {
        $nouvelEtat = (string) $demande->getEtatDemande();
        $nouveauPort = $demande->getPortMSAN();
        $portChange = $nouveauPort !== $ancienPort;

        if ($this->estCloturee($ancienEtat) && ($nouvelEtat !== $ancienEtat || $portChange)) {
            return sprintf(
                'Cette demande est clôturée (« %s ») : elle ne peut plus être modifiée.',
                self::LIBELLES[$ancienEtat] ?? $ancienEtat
            );
        }

        if (!$this->peutPasser($ancienEtat, $nouvelEtat)) {
            return sprintf(
                'Transition non autorisée : « %s » → « %s ».',
                self::LIBELLES[$ancienEtat] ?? $ancienEtat,
                self::LIBELLES[$nouvelEtat] ?? $nouvelEtat
            );
        }

        if (in_array($nouvelEtat, self::ETATS_AVEC_PORT, true) && null === $nouveauPort) {
            return sprintf(
                'Affectez un port avant de passer la demande à l\'état « %s ».',
                self::LIBELLES[$nouvelEtat]
            );
        }

        if ($portChange && null !== $nouveauPort && DemandeLigne::ETAT_REFUSEE !== $nouvelEtat
            && PortMSAN::ETAT_LIBRE !== $nouveauPort->getEtatPort()) {
            return sprintf(
                'Le port n°%d n\'est pas libre (état : %s).',
                $nouveauPort->getNumeroPort(),
                $nouveauPort->getEtatPort()
            );
        }

        return null;
    }

    /**
     * Applique les effets sur les ports : libération de l'ancien port, occupation du nouveau.
     */
    public function appliquer(DemandeLigne $demande, ?PortMSAN $ancienPort, ?\DateTime $maintenant = null): void
    {
        $nouveauPort = $demande->getPortMSAN();

        if (DemandeLigne::ETAT_REFUSEE === $demande->getEtatDemande()) {
            // Une demande refusée libère toujours son port
            foreach (array_filter([$ancienPort, $nouveauPort]) as $port) {
                $port->setEtatPort(PortMSAN::ETAT_LIBRE);
            }
            $demande->setPortMSAN(null);

            return;
        }

        if (null !== $ancienPort && $ancienPort !== $nouveauPort) {
            $ancienPort->setEtatPort(PortMSAN::ETAT_LIBRE);
        }

        if (null !== $nouveauPort) {
            // Un port affecté à une demande active ne reste jamais « libre »
            // (corrige aussi les anciennes données ; un port « en panne » garde son état)
            if ($nouveauPort !== $ancienPort || PortMSAN::ETAT_LIBRE === $nouveauPort->getEtatPort()) {
                $nouveauPort->setEtatPort(PortMSAN::ETAT_OCCUPE);
            }
            if ($nouveauPort !== $ancienPort) {
                $nouveauPort->setDateAffectation($maintenant ?? new \DateTime());
            }
        }
    }
}
