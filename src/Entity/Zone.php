<?php

namespace App\Entity;

use App\Repository\ZoneRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ZoneRepository::class)]
#[UniqueEntity(fields: ['nomZone'], message: 'Cette zone existe déjà.')]
class Zone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Le nom de la zone est obligatoire.')]
    #[Assert\Length(max: 100)]
    private ?string $nomZone = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Le code postal est obligatoire.')]
    #[Assert\Regex(pattern: '/^\d{4}$/', message: 'Le code postal doit contenir exactement 4 chiffres.')]
    private ?string $codePostal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomZone(): ?string
    {
        return $this->nomZone;
    }

    public function setNomZone(?string $nomZone): static
    {
        $this->nomZone = $nomZone;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }
}
