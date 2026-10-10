<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ReclamationRepository;
use App\Entity\Reclamation;
use App\Form\ReclamationUpdateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\QualificationLigne;
use App\Form\QualificationLigneType;
use App\Repository\QualificationLigneRepository;
use App\Service\GeoDistanceService;
use App\Service\XdslEstimationService;
use App\Repository\SiteMSANRepository;
use App\Repository\DemandeLigneRepository;
use App\Form\TechnicienPositionType;
use App\Service\ReclamationWorkflow;
use Symfony\Component\Form\FormError;
#[Route('/technicien')]
class TechnicienController extends AbstractController
{
    #[Route('/', name: 'technicien_dashboard')]
    #[IsGranted('ROLE_TECHNICIEN')]
    public function index(): Response
    {
        return $this->render('technicien/index.html.twig');
    }
    #[Route('/pannes-proximite', name: 'technicien_pannes_proximite')]
#[IsGranted('ROLE_TECHNICIEN')]
public function pannesProximite(ReclamationRepository $reclamationRepository): Response
{
    /** @var \App\Entity\Technicien $technicien */
    $technicien = $this->getUser();

    $pannes = [];

    if (null !== $technicien->getLatitudeActuelle() && null !== $technicien->getLongitudeActuelle()) {
        $pannes = $reclamationRepository->findPannesProches(
            $technicien->getLatitudeActuelle(),
            $technicien->getLongitudeActuelle(),
            15 // rayon en km
        );
    }

    return $this->render('technicien/pannes_proximite.html.twig', [
        'pannes' => $pannes,
        'technicien' => $technicien,
    ]);
}
#[Route('/mes-tickets', name: 'technicien_mes_tickets')]
#[IsGranted('ROLE_TECHNICIEN')]
public function mesTickets(ReclamationRepository $reclamationRepository): Response
{
    /** @var \App\Entity\Technicien $technicien */
    $technicien = $this->getUser();

    return $this->render('technicien/mes_tickets.html.twig', [
        'reclamations' => $reclamationRepository->findByTechnicien($technicien),
    ]);
}

#[Route('/ticket/{id}/mettre-a-jour', name: 'technicien_ticket_update')]
#[IsGranted('ROLE_TECHNICIEN')]
public function mettreAJourTicket(Reclamation $reclamation, Request $request, EntityManagerInterface $entityManager, ReclamationWorkflow $workflow): Response
{
    /** @var \App\Entity\Technicien $technicien */
    $technicien = $this->getUser();

    // Sécurité : un technicien ne peut modifier que SES tickets assignés
    if ($reclamation->getTechnicien() !== $technicien) {
        throw $this->createAccessDeniedException('Ce ticket ne vous est pas assigné.');
    }

    $ancienEtat = (string) $reclamation->getEtatTicket();

    $form = $this->createForm(ReclamationUpdateType::class, $reclamation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid() && null !== ($erreur = $workflow->verifier($reclamation, $ancienEtat, ReclamationWorkflow::ACTEUR_TECHNICIEN))) {
        $form->addError(new FormError($erreur));
    } elseif ($form->isSubmitted() && $form->isValid()) {
        $workflow->appliquer($reclamation, $ancienEtat);
        $entityManager->flush();

        $this->addFlash('success', 'Ticket ' . $reclamation->getCodeTicket() . ' mis à jour.');

        return $this->redirectToRoute('technicien_mes_tickets');
    }

    return $this->render('technicien/ticket_update.html.twig', [
        'reclamation' => $reclamation,
        'form' => $form,
        'etatActuel' => ReclamationWorkflow::LIBELLES[$ancienEtat] ?? $ancienEtat,
        'transitions' => array_map(
            fn (string $etat) => ReclamationWorkflow::LIBELLES[$etat],
            $workflow->etatsAccessibles($ancienEtat, ReclamationWorkflow::ACTEUR_TECHNICIEN)
        ),
    ]);
}
#[Route('/qualifications', name: 'technicien_qualifications')]
#[IsGranted('ROLE_TECHNICIEN')]
public function mesQualifications(QualificationLigneRepository $qualificationLigneRepository): Response
{
    /** @var \App\Entity\Technicien $technicien */
    $technicien = $this->getUser();

    $qualifications = $qualificationLigneRepository->findBy(
        ['technicien' => $technicien],
        ['dateTest' => 'DESC']
    );

    return $this->render('technicien/qualifications.html.twig', [
        'qualifications' => $qualifications,
    ]);
}

#[Route('/qualification/nouvelle', name: 'technicien_qualification_new')]
#[IsGranted('ROLE_TECHNICIEN')]
public function nouvelleQualification(
    Request $request,
    EntityManagerInterface $entityManager,
    GeoDistanceService $geoDistanceService,
    XdslEstimationService $xdslEstimationService
): Response {
    /** @var \App\Entity\Technicien $technicien */
    $technicien = $this->getUser();

    $qualification = new QualificationLigne();
    $qualification->setTechnicien($technicien);
    $qualification->setDateTest(new \DateTime());

    $form = $this->createForm(QualificationLigneType::class, $qualification);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $siteMSAN = $qualification->getPortMSAN()?->getSlotCard()?->getShelf()?->getSiteMSAN();

        $positionConnue = null !== $technicien->getLatitudeActuelle() && null !== $technicien->getLongitudeActuelle()
            && null !== $siteMSAN && null !== $siteMSAN->getLatitude() && null !== $siteMSAN->getLongitude();

        if (!$positionConnue) {
            // Sans position, la distance vaudrait 0 et le débit estimé serait faux (maximum théorique)
            $form->addError(new FormError(
                'Position GPS manquante (technicien ou site) : impossible d\'estimer le débit. Mettez à jour votre position.'
            ));

            return $this->render('technicien/qualification_new.html.twig', ['form' => $form], new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY));
        }

        $distance = $geoDistanceService->calculerDistanceKm(
            $technicien->getLatitudeActuelle(),
            $technicien->getLongitudeActuelle(),
            $siteMSAN->getLatitude(),
            $siteMSAN->getLongitude()
        );

        $qualification->setDistanceKm($distance);

        $debits = $xdslEstimationService->estimerDebit($qualification->getStandard(), $distance);
        $qualification->setChannelRateDown($debits['down']);
        $qualification->setChannelRateUp($debits['up']);

        $entityManager->persist($qualification);
        $entityManager->flush();

        $this->addFlash('success', sprintf(
            'Test enregistré. Distance : %s km — Débit estimé : %s / %s kbps',
            $qualification->getDistanceKm(),
            $qualification->getChannelRateDown(),
            $qualification->getChannelRateUp()
        ));

        return $this->redirectToRoute('technicien_qualifications');
    }

    return $this->render('technicien/qualification_new.html.twig', [
        'form' => $form,
    ]);
}
#[Route('/position', name: 'technicien_position')]
#[IsGranted('ROLE_TECHNICIEN')]
public function majPosition(Request $request, EntityManagerInterface $entityManager): Response
{
    /** @var \App\Entity\Technicien $technicien */
    $technicien = $this->getUser();

    $form = $this->createForm(TechnicienPositionType::class, $technicien);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this->addFlash('success', 'Position mise à jour.');

        return $this->redirectToRoute('technicien_dashboard');
    }

    return $this->render('technicien/position.html.twig', [
        'form' => $form,
    ]);
}
#[Route('/carte', name: 'technicien_carte')]
#[IsGranted('ROLE_TECHNICIEN')]
public function carte(SiteMSANRepository $siteMSANRepository, DemandeLigneRepository $demandeLigneRepository): Response
{
    $sites = $siteMSANRepository->findAllPourCarte();
    $demandes = $demandeLigneRepository->findEnAttentePourCarte();

    return $this->render('admin/carte.html.twig', [
        'sites' => $sites,
        'demandes' => $demandes,
    ]);
}
}