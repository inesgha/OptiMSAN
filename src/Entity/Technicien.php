<?php

namespace App\Entity;

use App\Repository\TechnicienRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TechnicienRepository::class)]
class Technicien extends Utilisateur
{

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Le matricule est obligatoire.')]
    #[Assert\Length(max: 30, maxMessage: 'Le matricule ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $matricule = null;

    #[ORM\Column]
    #[Assert\Range(min: -90, max: 90, notInRangeMessage: 'La latitude doit être comprise entre {{ min }} et {{ max }}.')]
    private ?float $latitudeActuelle = null;

    #[ORM\Column]
    #[Assert\Range(min: -180, max: 180, notInRangeMessage: 'La longitude doit être comprise entre {{ min }} et {{ max }}.')]
    private ?float $longitudeActuelle = null;

    #[ORM\Column]
    private ?bool $disponible = null;

    /**
     * @var Collection<int, QualificationLigne>
     */
    #[ORM\OneToMany(targetEntity: QualificationLigne::class, mappedBy: 'technicien')]
    private Collection $qualificationLignes;

    public function __construct()
    {
        $this->qualificationLignes = new ArrayCollection();
    }

    

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(?string $matricule): static
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getLatitudeActuelle(): ?float
    {
        return $this->latitudeActuelle;
    }

    public function setLatitudeActuelle(?float $latitudeActuelle): static
    {
        $this->latitudeActuelle = $latitudeActuelle;

        return $this;
    }

    public function getLongitudeActuelle(): ?float
    {
        return $this->longitudeActuelle;
    }

    public function setLongitudeActuelle(?float $longitudeActuelle): static
    {
        $this->longitudeActuelle = $longitudeActuelle;

        return $this;
    }

    public function isDisponible(): ?bool
    {
        return $this->disponible;
    }

    public function setDisponible(bool $disponible): static
    {
        $this->disponible = $disponible;

        return $this;
    }

    /**
     * @return Collection<int, QualificationLigne>
     */
    public function getQualificationLignes(): Collection
    {
        return $this->qualificationLignes;
    }

    public function addQualificationLigne(QualificationLigne $qualificationLigne): static
    {
        if (!$this->qualificationLignes->contains($qualificationLigne)) {
            $this->qualificationLignes->add($qualificationLigne);
            $qualificationLigne->setTechnicien($this);
        }

        return $this;
    }

    public function removeQualificationLigne(QualificationLigne $qualificationLigne): static
    {
        if ($this->qualificationLignes->removeElement($qualificationLigne)) {
            // set the owning side to null (unless already changed)
            if ($qualificationLigne->getTechnicien() === $this) {
                $qualificationLigne->setTechnicien(null);
            }
        }

        return $this;
    }
}
