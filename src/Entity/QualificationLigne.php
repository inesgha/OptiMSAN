<?php

namespace App\Entity;

use App\Repository\QualificationLigneRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QualificationLigneRepository::class)]
class QualificationLigne
{
    public const QUALITES = ['excellente', 'bonne', 'moyenne', 'faible'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Le standard est obligatoire.')]
    #[Assert\Length(max: 150)]
    private ?string $standard = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Choisissez la qualité globale.')]
    #[Assert\Choice(choices: self::QUALITES, message: 'Qualité invalide.')]
    private ?string $qualiteGlobale = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero(message: 'Le débit ne peut pas être négatif.')]
    private ?int $channelRateDown = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero(message: 'Le débit ne peut pas être négatif.')]
    private ?int $channelRateUp = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La marge SNR descendante est obligatoire.')]
    #[Assert\Range(min: -10, max: 60, notInRangeMessage: 'La marge SNR doit être comprise entre {{ min }} et {{ max }} dB.')]
    private ?float $snrMarginDown = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La marge SNR montante est obligatoire.')]
    #[Assert\Range(min: -10, max: 60, notInRangeMessage: 'La marge SNR doit être comprise entre {{ min }} et {{ max }} dB.')]
    private ?float $snrMarginUp = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'atténuation descendante est obligatoire.")]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: "L'atténuation doit être comprise entre {{ min }} et {{ max }} dB.")]
    private ?float $attenuationDown = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'atténuation montante est obligatoire.")]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: "L'atténuation doit être comprise entre {{ min }} et {{ max }} dB.")]
    private ?float $attenuationUp = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero(message: 'La distance ne peut pas être négative.')]
    private ?float $distanceKm = null;

    #[ORM\Column]
    private ?\DateTime $dateTest = null;

    #[ORM\ManyToOne(inversedBy: 'qualificationLignes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Technicien $technicien = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Choisissez le port testé.')]
    private ?PortMSAN $portMSAN = null;
    
    #[ORM\ManyToOne(inversedBy: 'qualificationLignes')]
private ?DemandeLigne $demandeLigne = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStandard(): ?string
    {
        return $this->standard;
    }

    public function setStandard(?string $standard): static
    {
        $this->standard = $standard;

        return $this;
    }

    public function getQualiteGlobale(): ?string
    {
        return $this->qualiteGlobale;
    }

    public function setQualiteGlobale(?string $qualiteGlobale): static
    {
        $this->qualiteGlobale = $qualiteGlobale;

        return $this;
    }

    public function getChannelRateDown(): ?int
    {
        return $this->channelRateDown;
    }

    public function setChannelRateDown(?int $channelRateDown): static
    {
        $this->channelRateDown = $channelRateDown;

        return $this;
    }

    public function getChannelRateUp(): ?int
    {
        return $this->channelRateUp;
    }

    public function setChannelRateUp(?int $channelRateUp): static
    {
        $this->channelRateUp = $channelRateUp;

        return $this;
    }

    public function getSnrMarginDown(): ?float
    {
        return $this->snrMarginDown;
    }

    public function setSnrMarginDown(?float $snrMarginDown): static
    {
        $this->snrMarginDown = $snrMarginDown;

        return $this;
    }

    public function getSnrMarginUp(): ?float
    {
        return $this->snrMarginUp;
    }

    public function setSnrMarginUp(?float $snrMarginUp): static
    {
        $this->snrMarginUp = $snrMarginUp;

        return $this;
    }

    public function getAttenuationDown(): ?float
    {
        return $this->attenuationDown;
    }

    public function setAttenuationDown(?float $attenuationDown): static
    {
        $this->attenuationDown = $attenuationDown;

        return $this;
    }

    public function getAttenuationUp(): ?float
    {
        return $this->attenuationUp;
    }

    public function setAttenuationUp(?float $attenuationUp): static
    {
        $this->attenuationUp = $attenuationUp;

        return $this;
    }

    public function getDistanceKm(): ?float
    {
        return $this->distanceKm;
    }

    public function setDistanceKm(?float $distanceKm): static
    {
        $this->distanceKm = $distanceKm;

        return $this;
    }

    public function getDateTest(): ?\DateTime
    {
        return $this->dateTest;
    }

    public function setDateTest(?\DateTime $dateTest): static
    {
        $this->dateTest = $dateTest;

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
    public function getDemandeLigne(): ?DemandeLigne
{
    return $this->demandeLigne;
}

public function setDemandeLigne(?DemandeLigne $demandeLigne): static
{
    $this->demandeLigne = $demandeLigne;

    return $this;
}
}