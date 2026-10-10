<?php

namespace App\Command;

use App\Entity\Utilisateur;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-client',
    description: 'Crée un compte Client dans la base de données',
)]
class CreateClientCommand extends Command
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

        $io->title('Création d\'un compte Client');

        $nom = $io->ask('Nom');
        $prenom = $io->ask('Prénom');
        $email = $io->ask('Email');
        $telephone = $io->ask('Téléphone (optionnel)', null);
        $password = $io->askHidden('Mot de passe');
        $cin = $io->ask('CIN');
        $adresse = $io->ask('Adresse');

        $existant = $this->entityManager
            ->getRepository(Utilisateur::class)
            ->findOneBy(['email' => $email]);

        if ($existant) {
            $io->error(sprintf('Un utilisateur avec l\'email "%s" existe déjà.', $email));
            return Command::FAILURE;
        }

        $client = new Client();
        $client->setNom($nom);
        $client->setPrenom($prenom);
        $client->setEmail($email);
        $client->setTelephone($telephone);
        $client->setCin($cin);
        $client->setAdresse($adresse);
        $client->setRoles(['ROLE_CLIENT']);
        $client->setIsVerified(true); // on force la vérification pour un compte de test

        // Mêmes règles que dans l'application (e-mail, CIN, téléphone, champs obligatoires…)

        if (mb_strlen((string) $password) < 6) {

            $io->error('Le mot de passe doit contenir au moins 6 caractères.');

            return Command::FAILURE;

        }


        $violations = $this->validator->validate($client);

        if (count($violations) > 0) {

            foreach ($violations as $violation) {

                $io->error($violation->getPropertyPath() . ' : ' . $violation->getMessage());

            }

            return Command::FAILURE;

        }

        $hashedPassword = $this->passwordHasher->hashPassword($client, $password);
        $client->setPassword($hashedPassword);

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        $io->success(sprintf('Client "%s %s" (%s) créé avec succès !', $prenom, $nom, $email));

        return Command::SUCCESS;
    }
}