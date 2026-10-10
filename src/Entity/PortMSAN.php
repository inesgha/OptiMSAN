<?php

namespace App\Entity;

use App\Repository\PortMSANRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Validator\CapaciteCarte;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PortMSANRepository::class)]
#[UniqueEntity(fields: ['slotCard', 'numeroPort'], errorPath: 'numeroPort', message: 'Ce numéro de port existe déjà sur cette carte.')]
#[CapaciteCarte]
class PortMSAN
{
    public const ETAT_LIBRE = 'libre';
    public const ETAT_OCCUPE = 'occupe';
    public const ETAT_PANNE = 'panne';
    public const ETAT_RESERVE = 'reserve';
    public const ETATS = [self::ETAT_LIBRE, self::ETAT_OCCUPE, self::ETAT_PANNE, self::ETAT_RESERVE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le numéro de port est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le numéro ne peut pas être négatif.')]
    private ?int $numeroPort = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Le type de port est obligatoire.')]
    #[Assert\Length(max: 255)]
    private ?string $typePort = null;

    #[ORM\Column(length: 250)]
    #[Assert\NotBlank(normalizer: 'trim', message: "L'état du port est obligatoire.")]
    #[Assert\Choice(choices: self::ETATS, message: 'État de port invalide.')]
    private ?string $etatPort = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date d'affectation est obligatoire.")]
    private ?\DateTime $dateAffectation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Choisissez une carte (slot).')]
    private ?SlotCard $slotCard = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroPort(): ?int
    {
        return $this->numeroPort;
    }

    public function setNumeroPort(?int $numeroPort): static
    {
        $this->numeroPort = $numeroPort;

        return $this;
    }

    public function getTypePort(): ?string
    {
        return $this->typePort;
    }

    public function setTypePort(?string $typePort): static
    {
        $this->typePort = $typePort;

        return $this;
    }

    public function getEtatPort(): ?string
    {
        return $this->etatPort;
    }

    public function setEtatPort(?string $etatPort): static
    {
        $this->etatPort = $etatPort;

        return $this;
    }

    public function getDateAffectation(): ?\DateTime
    {
        return $this->dateAffectation;
    }

    public function setDateAffectation(?\DateTime $dateAffectation): static
    {
        $this->dateAffectation = $dateAffectation;

        return $this;
    }

    public function getSlotCard(): ?SlotCard
    {
        return $this->slotCard;
    }

    public function setSlotCard(?SlotCard $slotCard): static
    {
        $this->slotCard = $slotCard;

        return $this;
    }
}