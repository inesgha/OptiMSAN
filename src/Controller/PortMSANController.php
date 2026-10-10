<?php

namespace App\Controller;

use App\Entity\PortMSAN;
use App\Form\PortMSANType;
use App\Repository\PortMSANRepository;
use App\Entity\DemandeLigne;
use App\Entity\Reclamation;
use App\Entity\QualificationLigne;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/port/m/s/a/n')]
#[IsGranted('ROLE_ADMIN')]
final class PortMSANController extends AbstractController
{
    #[Route(name: 'app_port_m_s_a_n_index', methods: ['GET'])]
    public function index(PortMSANRepository $portMSANRepository): Response
    {
        return $this->render('port_msan/index.html.twig', [
            'port_m_s_a_ns' => $portMSANRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_port_m_s_a_n_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $portMSAN = new PortMSAN();
        $form = $this->createForm(PortMSANType::class, $portMSAN);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($portMSAN);
            $entityManager->flush();

            return $this->redirectToRoute('app_port_m_s_a_n_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('port_msan/new.html.twig', [
            'port_m_s_a_n' => $portMSAN,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_port_m_s_a_n_show', methods: ['GET'])]
    public function show(PortMSAN $portMSAN): Response
    {
        return $this->render('port_msan/show.html.twig', [
            'port_m_s_a_n' => $portMSAN,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_port_m_s_a_n_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PortMSAN $portMSAN, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PortMSANType::class, $portMSAN);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_port_m_s_a_n_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('port_msan/edit.html.twig', [
            'port_m_s_a_n' => $portMSAN,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_port_m_s_a_n_delete', methods: ['POST'])]
    public function delete(Request $request, PortMSAN $portMSAN, EntityManagerInterface $entityManager): Response
    {
        // Intégrité : on ne supprime pas un port encore utilisé (évite une erreur 500 de clé étrangère)
        $nbDemandes = $entityManager->getRepository(DemandeLigne::class)->count(['portMSAN' => $portMSAN]);
        $nbReclamations = $entityManager->getRepository(Reclamation::class)->count(['portMSAN' => $portMSAN]);
        $nbTests = $entityManager->getRepository(QualificationLigne::class)->count(['portMSAN' => $portMSAN]);
        if ($nbDemandes + $nbReclamations + $nbTests > 0) {
            $this->addFlash('error', sprintf(
                'Suppression impossible : ce port est utilisé par %d demande(s) de ligne, %d réclamation(s) et %d test(s).',
                $nbDemandes,
                $nbReclamations,
                $nbTests
            ));

            return $this->redirectToRoute('app_port_m_s_a_n_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$portMSAN->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($portMSAN);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_port_m_s_a_n_index', [], Response::HTTP_SEE_OTHER);
    }
}
