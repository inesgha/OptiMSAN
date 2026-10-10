<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Client;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $client = new Client();
        $form = $this->createForm(RegistrationFormType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier que l'email n'est pas déjà utilisé
            $existant = $entityManager->getRepository(Utilisateur::class)
                ->findOneBy(['email' => $client->getEmail()]);

            if ($existant) {
                $this->addFlash('error', 'Un compte existe déjà avec cet email.');
                return $this->redirectToRoute('app_register');
            }

            $plainPassword = $form->get('plainPassword')->getData();

            $client->setPassword($userPasswordHasher->hashPassword($client, $plainPassword));
            $client->setRoles(['ROLE_CLIENT']);
            $client->setIsVerified(true); // simplifié : pas de vérification email pour l'instant

            $entityManager->persist($client);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}