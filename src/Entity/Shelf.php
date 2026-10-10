<?php

namespace App\Entity;

use App\Repository\ShelfRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ShelfRepository::class)]
#[UniqueEntity(fields: ['siteMSAN', 'numeroShelf'], errorPath: 'numeroShelf', message: 'Ce numéro de shelf existe déjà sur ce site.')]
class Shelf
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le numéro de shelf est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le numéro ne peut pas être négatif.')]
    private ?int $numeroShelf = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(normalizer: 'trim', message: "L'adresse IP est obligatoire.")]
    #[Assert\Ip(message: "L'adresse IP {{ value }} n'est pas valide.")]
    private ?string $adresseIpSub = null;

   #[ORM\ManyToOne]
    #[Assert\NotNull(message: 'Choisissez un site MSAN.')]
    private ?SiteMSAN $siteMSAN = null; 
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroShelf(): ?int
    {
        return $this->numeroShelf;
    }

    public function setNumeroShelf(?int $numeroShelf): static
    {
        $this->numeroShelf = $numeroShelf;

        return $this;
    }

    public function getAdresseIpSub(): ?string
    {
        return $this->adresseIpSub;
    }

    public function setAdresseIpSub(?string $adresseIpSub): static
    {
        $this->adresseIpSub = $adresseIpSub;

        return $this;
    }

    public function getSiteMSAN(): ?SiteMSAN
    {
        return $this->siteMSAN;
    }

    public function setSiteMSAN(?SiteMSAN $siteMSAN): static
    {
        $this->siteMSAN = $siteMSAN;
        return $this;
    }
}
