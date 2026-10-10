<?php

namespace App\Entity;

use App\Repository\DemandeLigneRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: DemandeLigneRepository::class)]
class DemandeLigne
{
    public const ETAT_EN_ATTENTE = 'en_attente';
    public const ETAT_VALIDEE = 'validee';
    public const ETAT_EN_COURS = 'en_cours';
    public const ETAT_INSTALLEE = 'installee';
    public const ETAT_REFUSEE = 'refusee';
    public const ETATS = [self::ETAT_EN_ATTENTE, self::ETAT_VALIDEE, self::ETAT_EN_COURS, self::ETAT_INSTALLEE, self::ETAT_REFUSEE];
    public const OFFRES = ['adsl_basique', 'vdsl_fibre', 'fibre_ftth', 'telephone_seul'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $codeDemande = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Choisissez une offre.')]
    #[Assert\Choice(choices: self::OFFRES, message: 'Offre invalide.')]
    private ?string $typeOffre = null;

    #[ORM\Column(length: 180)]
    #[Assert\Choice(choices: self::ETATS, message: 'État de demande invalide.')]
    private ?string $etatDemande = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(normalizer: 'trim', message: "L'adresse d'installation est obligatoire.")]
    #[Assert\Length(max: 150, maxMessage: "L'adresse d'installation ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $adresseInstallation = null;

    #[ORM\Column]
    private ?\DateTime $dateDemande = null;

    #[ORM\ManyToOne(inversedBy: 'demandeLignes')]
    private ?Client $client = null;

    #[ORM\ManyToOne]
    private ?PortMSAN $portMSAN = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeDemande(): ?string
    {
        return $this->codeDemande;
    }

    public function setCodeDemande(?string $codeDemande): static
    {
        $this->codeDemande = $codeDemande;

        return $this;
    }

    public function getTypeOffre(): ?string
    {
        return $this->typeOffre;
    }

    public function setTypeOffre(?string $typeOffre): static
    {
        $this->typeOffre = $typeOffre;

        return $this;
    }

    public function getEtatDemande(): ?string
    {
        return $this->etatDemande;
    }

    public function setEtatDemande(?string $etatDemande): static
    {
        $this->etatDemande = $etatDemande;

        return $this;
    }

    public function getAdresseInstallation(): ?string
    {
        return $this->adresseInstallation;
    }

    public function setAdresseInstallation(?string $adresseInstallation): static
    {
        $this->adresseInstallation = $adresseInstallation;

        return $this;
    }

    public function getDateDemande(): ?\DateTime
    {
        return $this->dateDemande;
    }

    public function setDateDemande(?\DateTime $dateDemande): static
    {
        $this->dateDemande = $dateDemande;

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

    public function getPortMSAN(): ?PortMSAN
    {
        return $this->portMSAN;
    }

    public function setPortMSAN(?PortMSAN $portMSAN): static
    {
        $this->portMSAN = $portMSAN;

        return $this;
    }
    /**
 * @var Collection<int, QualificationLigne>
 */
#[ORM\OneToMany(targetEntity: QualificationLigne::class, mappedBy: 'demandeLigne')]
private Collection $qualificationLignes;

#[ORM\Column(nullable: true)]
    #[Assert\Range(min: -90, max: 90, notInRangeMessage: 'La latitude doit être comprise entre {{ min }} et {{ max }}.')]
    private ?float $latitude = null;

#[ORM\Column(nullable: true)]
    #[Assert\Range(min: -180, max: 180, notInRangeMessage: 'La longitude doit être comprise entre {{ min }} et {{ max }}.')]
    private ?float $longitude = null;
public function __construct()
{
    $this->qualificationLignes = new ArrayCollection();
}
/**
 * @return Collection<int, QualificationLigne>
 */
public function getQualificationLignes(): Collection
{
    return $this->qualificationLignes;
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
}