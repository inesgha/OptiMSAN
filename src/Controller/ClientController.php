<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\DemandeLigne;
use App\Form\DemandeLigneType;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/client')]
class ClientController extends AbstractController
{
    #[Route('/', name: 'client_dashboard')]
    #[IsGranted('ROLE_CLIENT')]
    public function index(): Response
    {
        return $this->render('client/index.html.twig');
    }

    #[Route('/reclamation/nouvelle', name: 'client_reclamation_new')]
    #[IsGranted('ROLE_CLIENT')]
    public function deposerReclamation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation->setClient($this->getUser());
            $reclamation->setCodeTicket('TCK-' . strtoupper(uniqid()));
            $reclamation->setEtatTicket('ouvert');
            $reclamation->setDateCreation(new \DateTime());

            $entityManager->persist($reclamation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réclamation a été déposée avec succès. Ticket : ' . $reclamation->getCodeTicket());

            return $this->redirectToRoute('client_reclamations');
        }

        return $this->render('client/reclamation_new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reclamations', name: 'client_reclamations')]
    #[IsGranted('ROLE_CLIENT')]
    public function mesReclamations(): Response
    {
        /** @var \App\Entity\Client $client */
        $client = $this->getUser();

        return $this->render('client/reclamations.html.twig', [
            'reclamations' => $client->getReclamations(),
        ]);
    }
    #[Route('/demande-ligne/nouvelle', name: 'client_demande_ligne_new')]
#[IsGranted('ROLE_CLIENT')]
public function nouvelleDemandeLigne(Request $request, EntityManagerInterface $entityManager): Response
{
    $demandeLigne = new DemandeLigne();
    $form = $this->createForm(DemandeLigneType::class, $demandeLigne);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $demandeLigne->setClient($this->getUser());
        $demandeLigne->setCodeDemande('DEM-' . strtoupper(uniqid()));
        $demandeLigne->setEtatDemande('en_attente');
        $demandeLigne->setDateDemande(new \DateTime());

        $entityManager->persist($demandeLigne);
        $entityManager->flush();

        $this->addFlash('success', 'Votre demande a été enregistrée. Référence : ' . $demandeLigne->getCodeDemande());

        return $this->redirectToRoute('client_demandes_ligne');
    }

    return $this->render('client/demande_ligne_new.html.twig', [
        'form' => $form,
    ]);
}

#[Route('/demandes-ligne', name: 'client_demandes_ligne')]
#[IsGranted('ROLE_CLIENT')]
public function mesDemandesLigne(): Response
{
    /** @var \App\Entity\Client $client */
    $client = $this->getUser();

    return $this->render('client/demandes_ligne.html.twig', [
        'demandes' => $client->getDemandeLignes(),
    ]);
}
}