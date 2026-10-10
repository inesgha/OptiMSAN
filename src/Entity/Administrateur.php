<?php

namespace App\Entity;

use App\Repository\AdministrateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdministrateurRepository::class)]
class Administrateur extends Utilisateur
{
    #[ORM\Column]
    #[Assert\NotNull(message: "Le niveau d'accès est obligatoire.")]
    #[Assert\Range(min: 1, max: 3, notInRangeMessage: "Le niveau d'accès doit être compris entre {{ min }} et {{ max }}.")]
    private ?int $niveauAcces = null;

    public function getNiveauAcces(): ?int
    {
        return $this->niveauAcces;
    }

    public function setNiveauAcces(?int $niveauAcces): static
    {
        $this->niveauAcces = $niveauAcces;

        return $this;
    }
}
