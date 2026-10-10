<?php

namespace App\Twig;

use App\Service\DemandeLigneWorkflow;
use App\Service\ReclamationWorkflow;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Libellés lisibles et couleurs des états, utilisés par tous les templates.
 *
 *   {{ reclamation.typePanne|libelle('panne') }}  → « Pas de connexion Internet »
 *   {{ port.etatPort|ton('port') }}               → « success » (classe om-badge--success)
 *
 * Les états des tickets et des demandes reprennent les libellés des services workflow.
 */
class AppExtension extends AbstractExtension
{
    private const LIBELLES = [
        'ticket' => ReclamationWorkflow::LIBELLES,
        'demande' => DemandeLigneWorkflow::LIBELLES,
        'panne' => [
            'coupure_internet' => 'Pas de connexion Internet',
            'connexion_lente' => 'Connexion lente / instable',
            'coupure_telephone' => 'Ligne téléphonique coupée',
            'probleme_equipement' => 'Problème équipement (box, modem)',
            'autre' => 'Autre',
        ],
        'priorite' => [
            'faible' => 'Faible',
            'normale' => 'Normale',
            'urgente' => 'Urgente',
        ],
        'offre' => [
            'adsl_basique' => 'ADSL Basique',
            'vdsl_fibre' => 'VDSL Fibre',
            'fibre_ftth' => 'Fibre Optique (FTTH)',
            'telephone_seul' => 'Ligne téléphonique seule',
        ],
        'port' => [
            'libre' => 'Libre',
            'occupe' => 'Occupé',
            'panne' => 'En panne',
            'reserve' => 'Réservé',
        ],
        'qualite' => [
            'excellente' => 'Excellente',
            'bonne' => 'Bonne',
            'moyenne' => 'Moyenne',
            'faible' => 'Faible',
        ],
    ];

    /** Couleur (ton) de chaque état : info, warning, success, danger, neutral, violet, accent */
    private const TONS = [
        'ticket' => [
            'ouvert' => 'info',
            'en_cours' => 'warning',
            'resolu' => 'success',
            'ferme' => 'neutral',
        ],
        'demande' => [
            'en_attente' => 'warning',
            'validee' => 'info',
            'en_cours' => 'violet',
            'installee' => 'success',
            'refusee' => 'danger',
        ],
        'priorite' => [
            'faible' => 'neutral',
            'normale' => 'warning',
            'urgente' => 'danger',
        ],
        'port' => [
            'libre' => 'success',
            'occupe' => 'info',
            'panne' => 'danger',
            'reserve' => 'violet',
        ],
        'qualite' => [
            'excellente' => 'success',
            'bonne' => 'accent',
            'moyenne' => 'warning',
            'faible' => 'danger',
        ],
    ];

    public function getFilters(): array
    {
        return [
            new TwigFilter('libelle', $this->libelle(...)),
            new TwigFilter('ton', $this->ton(...)),
        ];
    }

    public function libelle(?string $valeur, string $type): string
    {
        if (null === $valeur || '' === $valeur) {
            return '—';
        }

        // Valeur inconnue : « valeur_brute » devient « Valeur brute »
        return self::LIBELLES[$type][$valeur] ?? ucfirst(str_replace('_', ' ', $valeur));
    }

    public function ton(?string $valeur, string $type): string
    {
        return self::TONS[$type][$valeur ?? ''] ?? 'neutral';
    }
}
