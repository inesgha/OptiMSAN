<?php

namespace App\Command;

use App\Entity\Utilisateur;
use App\Entity\Technicien;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-technicien',
    description: 'Crée un compte Technicien dans la base de données',
)]
class CreateTechnicienCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Création d\'un compte Technicien');

        $nom = $io->ask('Nom');
        $prenom = $io->ask('Prénom');
        $email = $io->ask('Email');
        $telephone = $io->ask('Téléphone (optionnel)', null);
        $password = $io->askHidden('Mot de passe');
        $matricule = $io->ask('Matricule (ex: TECH-001)');
        $latitude = (float) $io->ask('Latitude actuelle (ex: 36.8065)', '36.8065');
        $longitude = (float) $io->ask('Longitude actuelle (ex: 10.1815)', '10.1815');

        $existant = $this->entityManager
            ->getRepository(Utilisateur::class)
            ->findOneBy(['email' => $email]);

        if ($existant) {
            $io->error(sprintf('Un utilisateur avec l\'email "%s" existe déjà.', $email));
            return Command::FAILURE;
        }

        $technicien = new Technicien();
        $technicien->setNom($nom);
        $technicien->setPrenom($prenom);
        $technicien->setEmail($email);
        $technicien->setTelephone($telephone);
        $technicien->setMatricule($matricule);
        $technicien->setLatitudeActuelle($latitude);
        $technicien->setLongitudeActuelle($longitude);
        $technicien->setDisponible(true);
        $technicien->setRoles(['ROLE_TECHNICIEN']);

        // Mêmes règles que dans l'application (e-mail, CIN, téléphone, champs obligatoires…)

        if (mb_strlen((string) $password) < 6) {

            $io->error('Le mot de passe doit contenir au moins 6 caractères.');

            return Command::FAILURE;

        }


        $violations = $this->validator->validate($technicien);

        if (count($violations) > 0) {

            foreach ($violations as $violation) {

                $io->error($violation->getPropertyPath() . ' : ' . $violation->getMessage());

            }

            return Command::FAILURE;

        }

        $hashedPassword = $this->passwordHasher->hashPassword($technicien, $password);
        $technicien->setPassword($hashedPassword);

        $this->entityManager->persist($technicien);
        $this->entityManager->flush();

        $io->success(sprintf('Technicien "%s %s" (%s) créé avec succès !', $prenom, $nom, $email));

        return Command::SUCCESS;
    }
}