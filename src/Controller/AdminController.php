<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Reclamation;
use App\Form\ReclamationAssignType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\DemandeLigne;
use App\Form\DemandeLigneAssignType;
use App\Repository\DemandeLigneRepository;
use App\Repository\SiteMSANRepository;
use App\Repository\PortMSANRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Entity\PortMSAN;
use App\Entity\SlotCard;
use App\Repository\SlotCardRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\QualificationLigne;
use App\Form\QualificationLigneTestType;
use App\Entity\Administrateur;
use App\Entity\Technicien;
use App\Form\AdminCreateType;
use App\Form\TechnicienCreateType;
use App\Repository\UtilisateurRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\QualificationLigneRepository;
use App\Repository\TechnicienRepository;
use App\Service\DemandeLigneWorkflow;
use App\Service\ReclamationWorkflow;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        ReclamationRepository $reclamationRepository,
        DemandeLigneRepository $demandeLigneRepository,
        SiteMSANRepository $siteMSANRepository,
        TechnicienRepository $technicienRepository
    ): Response {
        $sites = $siteMSANRepository->findSaturationParSite();
        $saturation = $this->syntheseSaturation($sites);

        // Sites les plus chargés en premier
        foreach ($sites as &$site) {
            $site['taux'] = $site['total_ports'] > 0 ? round($site['ports_occupes'] / $site['total_ports'] * 100, 1) : 0;
        }
        unset($site);
        usort($sites, fn (array $a, array $b) => $b['taux'] <=> $a['taux']);

        $ticketsParEtat = $reclamationRepository->compterParEtat();
        $demandesParEtat = $demandeLigneRepository->compterParEtat();

        return $this->render('admin/index.html.twig', [
            'ticketsParEtat' => $ticketsParEtat,
            'ticketsOuverts' => $ticketsParEtat[Reclamation::ETAT_OUVERT] ?? 0,
            'ticketsEnCours' => $ticketsParEtat[Reclamation::ETAT_EN_COURS] ?? 0,
            'ticketsUrgents' => $reclamationRepository->count([
                'priorite' => 'urgente',
                'etatTicket' => [Reclamation::ETAT_OUVERT, Reclamation::ETAT_EN_COURS],
            ]),
            'demandesParEtat' => $demandesParEtat,
            'demandesEnAttente' => $demandesParEtat[DemandeLigne::ETAT_EN_ATTENTE] ?? 0,
            'tauxGlobal' => $saturation['tauxGlobal'],
            'totalPorts' => $saturation['totalPorts'],
            'totalOccupes' => $saturation['totalOccupes'],
            'techniciensDisponibles' => $technicienRepository->count(['disponible' => true]),
            'techniciensTotal' => $technicienRepository->count([]),
            'derniersTickets' => $reclamationRepository->findBy([], ['dateCreation' => 'DESC'], 6),
            'sitesCharges' => array_slice($sites, 0, 5),
        ]);
    }

    /**
     * Totaux de ports et taux d'occupation global à partir de findSaturationParSite().
     *
     * @return array{totalPorts: int, totalOccupes: int, tauxGlobal: float|int}
     */
    private function syntheseSaturation(array $sites): array
    {
        $totalPorts = 0;
        $totalOccupes = 0;

        foreach ($sites as $site) {
            $totalPorts += (int) $site['total_ports'];
            $totalOccupes += (int) $site['ports_occupes'];
        }

        return [
            'totalPorts' => $totalPorts,
            'totalOccupes' => $totalOccupes,
            'tauxGlobal' => $totalPorts > 0 ? round(($totalOccupes / $totalPorts) * 100, 1) : 0,
        ];
    }
    #[Route('/reclamations', name: 'admin_reclamations')]
#[IsGranted('ROLE_ADMIN')]
public function reclamations(ReclamationRepository $reclamationRepository): Response
{
    return $this->render('admin/reclamations.html.twig', [
        'reclamations' => $reclamationRepository->findBy([], ['dateCreation' => 'DESC']),
    ]);
}

#[Route('/reclamation/{id}/assigner', name: 'admin_reclamation_assign')]
#[IsGranted('ROLE_ADMIN')]
public function assignerReclamation(Reclamation $reclamation, Request $request, EntityManagerInterface $entityManager, ReclamationWorkflow $workflow): Response
{
    $ancienEtat = (string) $reclamation->getEtatTicket();

    $form = $this->createForm(ReclamationAssignType::class, $reclamation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $erreur = $workflow->verifier($reclamation, $ancienEtat, ReclamationWorkflow::ACTEUR_ADMIN);

        if (null !== $erreur) {
            $form->addError(new FormError($erreur));
        } else {
            $workflow->appliquer($reclamation, $ancienEtat);
            $entityManager->flush();

            $this->addFlash('success', 'Ticket ' . $reclamation->getCodeTicket() . ' mis à jour.');

            return $this->redirectToRoute('admin_reclamations');
        }
    }

    return $this->render('admin/reclamation_assign.html.twig', [
        'reclamation' => $reclamation,
        'form' => $form,
        'etatActuel' => ReclamationWorkflow::LIBELLES[$ancienEtat] ?? $ancienEtat,
        'transitions' => array_map(
            fn (string $etat) => ReclamationWorkflow::LIBELLES[$etat],
            $workflow->etatsAccessibles($ancienEtat, ReclamationWorkflow::ACTEUR_ADMIN)
        ),
    ]);
}
#[Route('/demandes-ligne', name: 'admin_demandes_ligne')]
#[IsGranted('ROLE_ADMIN')]
public function demandesLigne(DemandeLigneRepository $demandeLigneRepository): Response
{
    return $this->render('admin/demandes_ligne.html.twig', [
        'demandes' => $demandeLigneRepository->findBy([], ['dateDemande' => 'DESC']),
    ]);
}

#[Route('/saturation', name: 'admin_saturation')]
#[IsGranted('ROLE_ADMIN')]
public function saturation(SiteMSANRepository $siteMSANRepository): Response
{
    $sites = $siteMSANRepository->findSaturationParSite();
    $saturation = $this->syntheseSaturation($sites);

    return $this->render('admin/saturation.html.twig', [
        'sites' => $sites,
        'totalPorts' => $saturation['totalPorts'],
        'totalOccupes' => $saturation['totalOccupes'],
        'tauxGlobal' => $saturation['tauxGlobal'],
    ]);
}
#[Route('/export-ports', name: 'admin_export_ports')]
#[IsGranted('ROLE_ADMIN')]
public function exporterPorts(PortMSANRepository $portMSANRepository): StreamedResponse
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Ports MSAN');

    // En-têtes
    $sheet->fromArray(
        ['ID', 'Numéro Port', 'Type Port', 'État Port', 'Date Affectation', 'SlotCard ID', 'Numéro Slot'],
        null,
        'A1'
    );

    // Style des en-têtes
    $sheet->getStyle('A1:G1')->getFont()->setBold(true);

    $ports = $portMSANRepository->findAll();
    $row = 2;

    foreach ($ports as $port) {
        $sheet->fromArray([
            $port->getId(),
            $port->getNumeroPort(),
            $port->getTypePort(),
            $port->getEtatPort(),
            $port->getDateAffectation() ? $port->getDateAffectation()->format('Y-m-d') : '',
            $port->getSlotCard() ? $port->getSlotCard()->getId() : '',
            $port->getSlotCard() ? $port->getSlotCard()->getNumeroSlot() : '',
        ], null, "A$row");
        $row++;
    }

    // Ajuster la largeur des colonnes automatiquement
    foreach (range('A', 'G') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);

    $response = new StreamedResponse(function () use ($writer) {
        $writer->save('php://output');
    });

    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment;filename="ports_msan_' . date('Y-m-d') . '.xlsx"');
    $response->headers->set('Cache-Control', 'max-age=0');

    return $response;
}
#[Route('/import-ports', name: 'admin_import_ports')]
#[IsGranted('ROLE_ADMIN')]
public function importerPorts(
    Request $request,
    EntityManagerInterface $entityManager,
    SlotCardRepository $slotCardRepository,
    PortMSANRepository $portMSANRepository,
    ValidatorInterface $validator
): Response {
    if ($request->isMethod('POST')) {
        if (!$this->isCsrfTokenValid('import-ports', $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Formulaire expiré : veuillez réessayer.');
            return $this->redirectToRoute('admin_import_ports');
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('fichier_excel');

        if (!$file) {
            $this->addFlash('error', 'Aucun fichier sélectionné.');
            return $this->redirectToRoute('admin_import_ports');
        }

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $rows = $spreadsheet->getActiveSheet()->toArray();
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Fichier illisible : utilisez le modèle exporté (.xlsx).');
            return $this->redirectToRoute('admin_import_ports');
        }

        // On ignore la première ligne (en-têtes)
        array_shift($rows);

        $importes = 0;
        $erreurs = [];
        $clesVues = [];       // doublons à l'intérieur du fichier
        $ajoutsParCarte = []; // ports ajoutés par carte pendant cet import

        foreach ($rows as $index => $row) {
            $ligne = $index + 2; // numéro de ligne dans Excel

            if (empty($row[1]) && empty($row[2]) && empty($row[5])) {
                continue; // ligne vide
            }

            // Colonnes attendues : [ID(ignoré), NuméroPort, TypePort, EtatPort, DateAffectation, SlotCardID, NuméroSlot(ignoré)]
            $numeroPort = $row[1] ?? null;
            $typePort = trim((string) ($row[2] ?? ''));
            $etatPort = strtolower(trim((string) ($row[3] ?? ''))) ?: PortMSAN::ETAT_LIBRE;
            $dateAffectation = $row[4] ?? null;
            $slotCardId = $row[5] ?? null;

            if (!is_numeric($numeroPort) || (int) $numeroPort != $numeroPort || '' === $typePort || !is_numeric($slotCardId)) {
                $erreurs[] = sprintf('Ligne %d : numéro de port, type de port ou ID de carte manquant ou invalide.', $ligne);
                continue;
            }

            $slotCard = $slotCardRepository->find((int) $slotCardId);
            if (!$slotCard) {
                $erreurs[] = sprintf('Ligne %d : la carte (SlotCard) n°%s n\'existe pas.', $ligne, $slotCardId);
                continue;
            }

            $cle = $slotCard->getId() . '-' . (int) $numeroPort;
            if (isset($clesVues[$cle])) {
                $erreurs[] = sprintf('Ligne %d : le port n°%d apparaît deux fois pour la même carte.', $ligne, $numeroPort);
                continue;
            }

            try {
                $date = $dateAffectation ? new \DateTime((string) $dateAffectation) : new \DateTime();
            } catch (\Exception) {
                $erreurs[] = sprintf('Ligne %d : date d\'affectation « %s » invalide.', $ligne, $dateAffectation);
                continue;
            }

            $port = (new PortMSAN())
                ->setNumeroPort((int) $numeroPort)
                ->setTypePort($typePort)
                ->setEtatPort($etatPort)
                ->setDateAffectation($date)
                ->setSlotCard($slotCard);

            $violations = $validator->validate($port);
            if (count($violations) > 0) {
                $erreurs[] = sprintf('Ligne %d : %s', $ligne, $violations[0]->getMessage());
                continue;
            }

            // Règle de capacité : une carte ne peut pas avoir plus de ports que nombrePortsTotal
            $occupation = $portMSANRepository->count(['slotCard' => $slotCard]) + ($ajoutsParCarte[$slotCard->getId()] ?? 0);
            if ($occupation >= $slotCard->getNombrePortsTotal()) {
                $erreurs[] = sprintf('Ligne %d : la carte n°%d est pleine (%d ports maximum).', $ligne, $slotCard->getNumeroSlot(), $slotCard->getNombrePortsTotal());
                continue;
            }

            $entityManager->persist($port);
            $clesVues[$cle] = true;
            $ajoutsParCarte[$slotCard->getId()] = ($ajoutsParCarte[$slotCard->getId()] ?? 0) + 1;
            $importes++;
        }

        $entityManager->flush();

        $this->addFlash('success', sprintf('%d port(s) importé(s). %d ligne(s) rejetée(s).', $importes, count($erreurs)));
        foreach (array_slice($erreurs, 0, 10) as $erreur) {
            $this->addFlash('error', $erreur);
        }
        if (count($erreurs) > 10) {
            $this->addFlash('error', sprintf('… et %d autre(s) erreur(s).', count($erreurs) - 10));
        }

        return $this->redirectToRoute('admin_import_ports');
    }

    return $this->render('admin/import_ports.html.twig');
}
#[Route('/demande-ligne/{id}/tester', name: 'admin_demande_ligne_test_new')]
#[IsGranted('ROLE_ADMIN')]
public function testerDemandeLigne(DemandeLigne $demandeLigne, Request $request, EntityManagerInterface $entityManager): Response
{
    $qualification = new QualificationLigne();
    $qualification->setDemandeLigne($demandeLigne);
    $qualification->setDateTest(new \DateTime());

    $form = $this->createForm(QualificationLigneTestType::class, $qualification);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($qualification);
        $entityManager->flush();

        $this->addFlash('success', 'Test de ligne enregistré.');

        return $this->redirectToRoute('admin_demande_ligne_assign', ['id' => $demandeLigne->getId()]);
    }

    return $this->render('admin/demande_ligne_test.html.twig', [
        'demande' => $demandeLigne,
        'form' => $form,
    ]);
}
#[Route('/demande-ligne/{id}/affecter', name: 'admin_demande_ligne_assign')]
#[IsGranted('ROLE_ADMIN')]
public function affecterDemandeLigne(DemandeLigne $demandeLigne, Request $request, EntityManagerInterface $entityManager, DemandeLigneWorkflow $workflow): Response
{
    $ancienEtat = (string) $demandeLigne->getEtatDemande();
    $ancienPort = $demandeLigne->getPortMSAN();

    $form = $this->createForm(DemandeLigneAssignType::class, $demandeLigne);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid() && null !== ($erreur = $workflow->verifier($demandeLigne, $ancienEtat, $ancienPort))) {
        $form->addError(new FormError($erreur));
    } elseif ($form->isSubmitted() && $form->isValid()) {
        $workflow->appliquer($demandeLigne, $ancienPort);
        $entityManager->flush();

        $this->addFlash('success', 'Demande ' . $demandeLigne->getCodeDemande() . ' mise à jour.');

        return $this->redirectToRoute('admin_demandes_ligne');
    }

    return $this->render('admin/demande_ligne_assign.html.twig', [
        'demande' => $demandeLigne,
        'form' => $form,
        'tests' => $demandeLigne->getQualificationLignes(),
        'etatActuel' => DemandeLigneWorkflow::LIBELLES[$ancienEtat] ?? $ancienEtat,
        'transitions' => array_map(
            fn (string $etat) => DemandeLigneWorkflow::LIBELLES[$etat],
            $workflow->etatsAccessibles($ancienEtat)
        ),
    ]);
}
#[Route('/carte', name: 'admin_carte')]
#[IsGranted('ROLE_ADMIN')]
public function carte(SiteMSANRepository $siteMSANRepository, DemandeLigneRepository $demandeLigneRepository): Response
{
    $sites = $siteMSANRepository->findAllPourCarte();
    $demandes = $demandeLigneRepository->findEnAttentePourCarte();

    return $this->render('admin/carte.html.twig', [
        'sites' => $sites,
        'demandes' => $demandes,
    ]);
}
#[Route('/equipe', name: 'admin_equipe')]
#[IsGranted('ROLE_ADMIN')]
public function equipe(UtilisateurRepository $utilisateurRepository): Response
{
    $administrateurs = $utilisateurRepository->findAllAdministrateurs();
    $techniciens = $utilisateurRepository->findAllTechniciens();

    return $this->render('admin/equipe.html.twig', [
        'administrateurs' => $administrateurs,
        'techniciens' => $techniciens,
    ]);
}

#[Route('/equipe/admin/nouveau', name: 'admin_create_admin')]
#[IsGranted('ROLE_ADMIN')]
public function creerAdmin(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
{
    $admin = new Administrateur();
    $form = $this->createForm(AdminCreateType::class, $admin);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $plainPassword = $form->get('plainPassword')->getData();
        $admin->setPassword($passwordHasher->hashPassword($admin, $plainPassword));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);

        $entityManager->persist($admin);
        $entityManager->flush();

        $this->addFlash('success', 'Administrateur créé avec succès.');

        return $this->redirectToRoute('admin_equipe');
    }

    return $this->render('admin/equipe_create.html.twig', [
        'form' => $form,
        'titre' => 'Nouvel Administrateur',
    ]);
}

#[Route('/equipe/technicien/nouveau', name: 'admin_create_technicien')]
#[IsGranted('ROLE_ADMIN')]
public function creerTechnicien(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
{
    $technicien = new Technicien();
    $form = $this->createForm(TechnicienCreateType::class, $technicien);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $plainPassword = $form->get('plainPassword')->getData();
        $technicien->setPassword($passwordHasher->hashPassword($technicien, $plainPassword));
        $technicien->setRoles(['ROLE_TECHNICIEN']);
        $technicien->setIsVerified(true);
        $technicien->setDisponible(true);

        $entityManager->persist($technicien);
        $entityManager->flush();

        $this->addFlash('success', 'Technicien créé avec succès.');

        return $this->redirectToRoute('admin_equipe');
    }

    return $this->render('admin/equipe_create.html.twig', [
        'form' => $form,
        'titre' => 'Nouveau Technicien',
    ]);
}

#[Route('/equipe/{id}/supprimer', name: 'admin_equipe_delete', methods: ['POST'])]
#[IsGranted('ROLE_ADMIN')]
public function supprimerUtilisateur(int $id, Request $request, EntityManagerInterface $entityManager, UtilisateurRepository $utilisateurRepository): Response
{
    $utilisateur = $utilisateurRepository->find($id);

    if (!$utilisateur) {
        throw $this->createNotFoundException();
    }

    // Règle de sécurité : un admin ne peut jamais supprimer un autre admin
    if (in_array('ROLE_ADMIN', $utilisateur->getRoles(), true)) {
        $this->addFlash('error', 'Un compte Administrateur ne peut pas être supprimé par un autre Administrateur.');
        return $this->redirectToRoute('admin_equipe');
    }

    // Règle d'intégrité : un technicien qui a des tickets ou des tests enregistrés est conservé
    if ($utilisateur instanceof Technicien) {
        $nbTickets = $entityManager->getRepository(Reclamation::class)->count(['technicien' => $utilisateur]);
        $nbTests = $entityManager->getRepository(QualificationLigne::class)->count(['technicien' => $utilisateur]);

        if ($nbTickets + $nbTests > 0) {
            $this->addFlash('error', sprintf(
                'Suppression impossible : ce technicien a %d ticket(s) et %d test(s) enregistrés.',
                $nbTickets,
                $nbTests
            ));

            return $this->redirectToRoute('admin_equipe');
        }
    }

    if ($this->isCsrfTokenValid('delete-user-' . $utilisateur->getId(), $request->getPayload()->getString('_token'))) {
        $entityManager->remove($utilisateur);
        $entityManager->flush();
        $this->addFlash('success', 'Compte supprimé.');
    }

    return $this->redirectToRoute('admin_equipe');
}
}