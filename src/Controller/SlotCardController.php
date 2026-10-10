<?php

namespace App\Controller;

use App\Entity\SlotCard;
use App\Form\SlotCardType;
use App\Repository\SlotCardRepository;
use App\Entity\PortMSAN;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/slot/card')]
#[IsGranted('ROLE_ADMIN')]
final class SlotCardController extends AbstractController
{
    #[Route(name: 'app_slot_card_index', methods: ['GET'])]
    public function index(SlotCardRepository $slotCardRepository): Response
    {
        return $this->render('slot_card/index.html.twig', [
            'slot_cards' => $slotCardRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_slot_card_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $slotCard = new SlotCard();
        $form = $this->createForm(SlotCardType::class, $slotCard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($slotCard);
            $entityManager->flush();

            return $this->redirectToRoute('app_slot_card_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('slot_card/new.html.twig', [
            'slot_card' => $slotCard,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_slot_card_show', methods: ['GET'])]
    public function show(SlotCard $slotCard): Response
    {
        return $this->render('slot_card/show.html.twig', [
            'slot_card' => $slotCard,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_slot_card_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SlotCard $slotCard, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SlotCardType::class, $slotCard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_slot_card_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('slot_card/edit.html.twig', [
            'slot_card' => $slotCard,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_slot_card_delete', methods: ['POST'])]
    public function delete(Request $request, SlotCard $slotCard, EntityManagerInterface $entityManager): Response
    {
        // Intégrité : on ne supprime pas un élément encore utilisé (évite une erreur 500 de clé étrangère)
        $nbLies = $entityManager->getRepository(PortMSAN::class)->count(['slotCard' => $slotCard]);
        if ($nbLies > 0) {
            $this->addFlash('error', sprintf('Suppression impossible : cette carte contient %d port(s).', $nbLies));

            return $this->redirectToRoute('app_slot_card_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$slotCard->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($slotCard);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_slot_card_index', [], Response::HTTP_SEE_OTHER);
    }
}
