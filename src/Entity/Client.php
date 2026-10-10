<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client extends Utilisateur
{
    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Le CIN est obligatoire.')]
    #[Assert\Regex(pattern: '/^\d{8}$/', message: 'Le CIN doit contenir exactement 8 chiffres.')]
    private ?string $cin = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(normalizer: 'trim', message: "L'adresse est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "L'adresse ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $adresse = null;

    /**
     * @var Collection<int, Reclamation>
     */
    #[ORM\OneToMany(targetEntity: Reclamation::class, mappedBy: 'client')]
    private Collection $reclamations;

    /**
     * @var Collection<int, DemandeLigne>
     */
    #[ORM\OneToMany(targetEntity: DemandeLigne::class, mappedBy: 'client')]
    private Collection $demandeLignes;

    public function __construct()
    {
        $this->reclamations = new ArrayCollection();
        $this->demandeLignes = new ArrayCollection();
    }

    public function getCin(): ?string
    {
        return $this->cin;
    }

    public function setCin(?string $cin): static
    {
        $this->cin = $cin;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * @return Collection<int, Reclamation>
     */
    public function getReclamations(): Collection
    {
        return $this->reclamations;
    }

    public function addReclamation(Reclamation $reclamation): static
    {
        if (!$this->reclamations->contains($reclamation)) {
            $this->reclamations->add($reclamation);
            $reclamation->setClient($this);
        }

        return $this;
    }

    public function removeReclamation(Reclamation $reclamation): static
    {
        if ($this->reclamations->removeElement($reclamation)) {
            if ($reclamation->getClient() === $this) {
                $reclamation->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DemandeLigne>
     */
    public function getDemandeLignes(): Collection
    {
        return $this->demandeLignes;
    }

    public function addDemandeLigne(DemandeLigne $demandeLigne): static
    {
        if (!$this->demandeLignes->contains($demandeLigne)) {
            $this->demandeLignes->add($demandeLigne);
            $demandeLigne->setClient($this);
        }

        return $this;
    }

    public function removeDemandeLigne(DemandeLigne $demandeLigne): static
    {
        if ($this->demandeLignes->removeElement($demandeLigne)) {
            if ($demandeLigne->getClient() === $this) {
                $demandeLigne->setClient(null);
            }
        }

        return $this;
    }
}