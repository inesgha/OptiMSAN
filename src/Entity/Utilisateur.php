<?php

// src/Entity/Utilisateur.php
namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'role_type', type: 'string')]
#[ORM\DiscriminatorMap([
    'technicien' => Technicien::class,
    'administrateur' => Administrateur::class,
    'client' => Client::class,
])]
#[UniqueEntity(fields: ['email'], entityClass: Utilisateur::class, message: 'Un compte existe déjà avec cette adresse e-mail.')]
abstract class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 50, maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(normalizer: 'trim', message: 'Le prénom est obligatoire.')]
    #[Assert\Length(max: 50, maxMessage: 'Le prénom ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $prenom = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(normalizer: 'trim', message: "L'adresse e-mail est obligatoire.")]
    #[Assert\Email(message: "L'adresse e-mail {{ value }} n'est pas valide.")]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: '/^\d{8}$/', message: 'Le numéro de téléphone doit contenir exactement 8 chiffres.')]
    private ?string $telephone = null;

    #[ORM\Column]
    private bool $isVerified = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}