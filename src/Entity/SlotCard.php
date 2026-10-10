<?php

namespace App\Entity;

use App\Repository\SlotCardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Validator\CapaciteCarte;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SlotCardRepository::class)]
#[UniqueEntity(fields: ['shelf', 'numeroSlot'], errorPath: 'numeroSlot', message: 'Ce numéro de slot existe déjà sur ce shelf.')]
#[CapaciteCarte]
class SlotCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le numéro de slot est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le numéro ne peut pas être négatif.')]
    private ?int $numeroSlot = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Le type de carte est obligatoire.')]
    #[Assert\Length(max: 150)]
    private ?string $typeCarte = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le nombre de ports est obligatoire.')]
    #[Assert\Range(min: 1, max: 128, notInRangeMessage: 'Le nombre de ports doit être compris entre {{ min }} et {{ max }}.')]
    private ?int $nombrePortsTotal = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Choisissez un shelf.')]
    private ?Shelf $shelf = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroSlot(): ?int
    {
        return $this->numeroSlot;
    }

    public function setNumeroSlot(?int $numeroSlot): static
    {
        $this->numeroSlot = $numeroSlot;

        return $this;
    }

    public function getTypeCarte(): ?string
    {
        return $this->typeCarte;
    }

    public function setTypeCarte(?string $typeCarte): static
    {
        $this->typeCarte = $typeCarte;

        return $this;
    }

    public function getNombrePortsTotal(): ?int
    {
        return $this->nombrePortsTotal;
    }

    public function setNombrePortsTotal(?int $nombrePortsTotal): static
    {
        $this->nombrePortsTotal = $nombrePortsTotal;

        return $this;
    }

    public function getShelf(): ?Shelf
    {
        return $this->shelf;
    }

    public function setShelf(?Shelf $shelf): static
    {
        $this->shelf = $shelf;

        return $this;
    }
}