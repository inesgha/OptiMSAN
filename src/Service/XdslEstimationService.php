<?php

namespace App\Service;

class XdslEstimationService
{
    public const DEBIT_MIN_DOWN = 128;
    public const DEBIT_MIN_UP = 64;

    /** [débit max théorique en kbps à 0 km, constante de décroissance en km] — du plus précis au plus général */
    private const PROFILS = [
        'vdsl2' => ['down' => 100000, 'up' => 20000, 'decay' => 0.6],
        'adsl2+' => ['down' => 24000, 'up' => 1000, 'decay' => 1.8],
        'adsl2' => ['down' => 12000, 'up' => 1000, 'decay' => 1.8],
        'adsl' => ['down' => 8000, 'up' => 800, 'decay' => 1.8],
    ];

    /** Profil utilisé si le standard n'est pas reconnu */
    private const PROFIL_PAR_DEFAUT = ['down' => 10000, 'up' => 1000, 'decay' => 1.5];

    /**
     * Estime le débit descendant/montant (kbps) en fonction du standard et de la distance.
     * Modèle simplifié : décroissance exponentielle, avec un débit plancher.
     *
     * @return array{down: int, up: int}
     *
     * @throws \InvalidArgumentException si la distance est négative
     */
    public function estimerDebit(string $standard, float $distanceKm): array
    {
        if ($distanceKm < 0) {
            throw new \InvalidArgumentException('La distance ne peut pas être négative.');
        }

        $profil = $this->profilPour($standard);
        $facteur = exp(-$distanceKm / $profil['decay']);

        $down = (int) max(self::DEBIT_MIN_DOWN, round($profil['down'] * $facteur));
        $up = (int) max(self::DEBIT_MIN_UP, round($profil['up'] * $facteur));

        return ['down' => $down, 'up' => $up];
    }

    /**
     * @return array{down: int, up: int, decay: float}
     */
    private function profilPour(string $standard): array
    {
        $standard = strtolower(trim($standard));

        foreach (self::PROFILS as $cle => $valeurs) {
            if (str_contains($standard, $cle)) {
                return $valeurs;
            }
        }

        return self::PROFIL_PAR_DEFAUT;
    }
}
