<?php

namespace App\Controller;

use App\Entity\SiteMSAN;
use App\Form\SiteMSANType;
use App\Repository\SiteMSANRepository;
use App\Entity\Shelf;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/sites')]
#[IsGranted('ROLE_ADMIN')]
final class SiteMSANController extends AbstractController
{
    #[Route(name: 'app_site_m_s_a_n_index', methods: ['GET'])]

    public function index(SiteMSANRepository $siteMSANRepository): Response
    {
        return $this->render('site_msan/index.html.twig', [
            'site_m_s_a_ns' => $siteMSANRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_site_m_s_a_n_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $siteMSAN = new SiteMSAN();
        $form = $this->createForm(SiteMSANType::class, $siteMSAN);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($siteMSAN);
            $entityManager->flush();

            return $this->redirectToRoute('app_site_m_s_a_n_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('site_msan/new.html.twig', [
            'site_m_s_a_n' => $siteMSAN,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_site_m_s_a_n_show', methods: ['GET'])]
    public function show(SiteMSAN $siteMSAN): Response
    {
        return $this->render('site_msan/show.html.twig', [
            'site_m_s_a_n' => $siteMSAN,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_site_m_s_a_n_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SiteMSAN $siteMSAN, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SiteMSANType::class, $siteMSAN);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_site_m_s_a_n_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('site_msan/edit.html.twig', [
            'site_m_s_a_n' => $siteMSAN,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_site_m_s_a_n_delete', methods: ['POST'])]
    public function delete(Request $request, SiteMSAN $siteMSAN, EntityManagerInterface $entityManager): Response
    {
        // Intégrité : on ne supprime pas un élément encore utilisé (évite une erreur 500 de clé étrangère)
        $nbLies = $entityManager->getRepository(Shelf::class)->count(['siteMSAN' => $siteMSAN]);
        if ($nbLies > 0) {
            $this->addFlash('error', sprintf('Suppression impossible : ce site contient %d shelf(s).', $nbLies));

            return $this->redirectToRoute('app_site_m_s_a_n_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$siteMSAN->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($siteMSAN);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_site_m_s_a_n_index', [], Response::HTTP_SEE_OTHER);
    }
}
