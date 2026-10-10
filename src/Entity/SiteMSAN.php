<?php

namespace App\Entity;

use App\Repository\SiteMSANRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SiteMSANRepository::class)]
#[UniqueEntity(fields: ['adresseIp'], message: 'Cette adresse IP est déjà utilisée par un autre site.')]
class SiteMSAN
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Le nom du site est obligatoire.')]
    #[Assert\Length(max: 200)]
    private ?string $nomSite = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(normalizer: 'trim', message: "L'adresse IP est obligatoire.")]
    #[Assert\Ip(message: "L'adresse IP {{ value }} n'est pas valide.")]
    private ?string $adresseIp = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Le type de MSAN est obligatoire.')]
    #[Assert\Length(max: 100)]
    private ?string $typeMSAN = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Le constructeur est obligatoire.')]
    #[Assert\Length(max: 150)]
    private ?string $constructeur = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Choisissez l'emplacement du site sur la carte.")]
    #[Assert\Range(min: -90, max: 90, notInRangeMessage: 'La latitude doit être comprise entre {{ min }} et {{ max }}.')]
    private ?float $latitude = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Choisissez l'emplacement du site sur la carte.")]
    #[Assert\Range(min: -180, max: 180, notInRangeMessage: 'La longitude doit être comprise entre {{ min }} et {{ max }}.')]
    private ?float $longitude = null;

    #[ORM\ManyToOne]
    #[Assert\NotNull(message: 'Choisissez une zone.')]
    private ?Zone $zone = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSite(): ?string
    {
        return $this->nomSite;
    }

    public function setNomSite(?string $nomSite): static
    {
        $this->nomSite = $nomSite;

        return $this;
    }

    public function getAdresseIp(): ?string
    {
        return $this->adresseIp;
    }

    public function setAdresseIp(?string $adresseIp): static
    {
        $this->adresseIp = $adresseIp;

        return $this;
    }

    public function getTypeMSAN(): ?string
    {
        return $this->typeMSAN;
    }

    public function setTypeMSAN(?string $typeMSAN): static
    {
        $this->typeMSAN = $typeMSAN;

        return $this;
    }

    public function getConstructeur(): ?string
    {
        return $this->constructeur;
    }

    public function setConstructeur(?string $constructeur): static
    {
        $this->constructeur = $constructeur;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): static
    {
        $this->zone = $zone;

        return $this;
    }
}