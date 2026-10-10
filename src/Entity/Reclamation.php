<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    public const ETAT_OUVERT = 'ouvert';
    public const ETAT_EN_COURS = 'en_cours';
    public const ETAT_RESOLU = 'resolu';
    public const ETAT_FERME = 'ferme';
    public const ETATS = [self::ETAT_OUVERT, self::ETAT_EN_COURS, self::ETAT_RESOLU, self::ETAT_FERME];
    public const TYPES_PANNE = ['coupure_internet', 'connexion_lente', 'coupure_telephone', 'probleme_equipement', 'autre'];
    public const PRIORITES = ['faible', 'normale', 'urgente'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $codeTicket = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Choisissez le type de panne.')]
    #[Assert\Choice(choices: self::TYPES_PANNE, message: 'Type de panne invalide.')]
    private ?string $typePanne = null;

    #[ORM\Column(length: 255, nullable: true)]
    // Longueur minimale contrôlée dans ReclamationType (formulaire client)
    #[Assert\Length(max: 255, maxMessage: 'La description ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $description = null;

    /** Notes saisies par le technicien : ne remplacent plus la description du client. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: 'Les notes ne doivent pas dépasser {{ limit }} caractères.')]
    private ?string $notesIntervention = null;

    #[ORM\Column(length: 250)]
    #[Assert\Choice(choices: self::ETATS, message: 'État de ticket invalide.')]
    private ?string $etatTicket = null;

    #[ORM\Column(length: 250)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Choisissez une priorité.')]
    #[Assert\Choice(choices: self::PRIORITES, message: 'Priorité invalide.')]
    private ?string $priorite = null;

    #[ORM\Column]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateResolution = null;

    #[ORM\ManyToOne (inversedBy: 'reclamations')]
    private ?Client $client = null;

    #[ORM\ManyToOne]
    private ?Technicien $technicien = null;

    #[ORM\ManyToOne]
    private ?PortMSAN $portMSAN = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeTicket(): ?string
    {
        return $this->codeTicket;
    }

    public function setCodeTicket(?string $codeTicket): static
    {
        $this->codeTicket = $codeTicket;

        return $this;
    }

    public function getTypePanne(): ?string
    {
        return $this->typePanne;
    }

    public function setTypePanne(?string $typePanne): static
    {
        $this->typePanne = $typePanne;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getNotesIntervention(): ?string
    {
        return $this->notesIntervention;
    }

    public function setNotesIntervention(?string $notesIntervention): static
    {
        $this->notesIntervention = $notesIntervention;

        return $this;
    }

    public function getEtatTicket(): ?string
    {
        return $this->etatTicket;
    }

    public function setEtatTicket(?string $etatTicket): static
    {
        $this->etatTicket = $etatTicket;

        return $this;
    }

    public function getPriorite(): ?string
    {
        return $this->priorite;
    }

    public function setPriorite(?string $priorite): static
    {
        $this->priorite = $priorite;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateResolution(): ?\DateTime
    {
        return $this->dateResolution;
    }

    public function setDateResolution(?\DateTime $dateResolution): static
    {
        $this->dateResolution = $dateResolution;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getTechnicien(): ?Technicien
    {
        return $this->technicien;
    }

    public function setTechnicien(?Technicien $technicien): static
    {
        $this->technicien = $technicien;

        return $this;
    }

    public function getPortMSAN(): ?PortMSAN
    {
        return $this->portMSAN;
    }

    public function setPortMSAN(?PortMSAN $portMSAN): static
    {
        $this->portMSAN = $portMSAN;

        return $this;
    }
}