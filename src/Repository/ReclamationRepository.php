<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\GeoDistanceService;
use App\Entity\Technicien;

/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private GeoDistanceService $geoDistance)
    {
        parent::__construct($registry, Reclamation::class);
    }

    //    /**
    //     * @return Reclamation[] Returns an array of Reclamation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reclamation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * Tickets « ouverts » dont le site MSAN se trouve à moins de $rayonKm du technicien,
     * triés du plus proche au plus éloigné. La distance est calculée en PHP avec
     * GeoDistanceService (même formule que la qualification, compatible MariaDB et SQLite).
     *
     * @return array<int, array<string, mixed>>
     */
    public function findPannesProches(float $latTechnicien, float $lngTechnicien, float $rayonKm = 15): array
    {
        $sql = "
            SELECT
                r.id,
                r.code_ticket,
                r.type_panne,
                r.description,
                r.priorite,
                r.etat_ticket,
                r.date_creation,
                s.nom_site,
                s.latitude,
                s.longitude
            FROM reclamation r
            INNER JOIN port_msan p ON r.port_msan_id = p.id
            INNER JOIN slot_card sc ON p.slot_card_id = sc.id
            INNER JOIN shelf sh ON sc.shelf_id = sh.id
            INNER JOIN site_msan s ON sh.site_msan_id = s.id
            WHERE r.etat_ticket = 'ouvert'
        ";

        $pannes = [];
        foreach ($this->getEntityManager()->getConnection()->executeQuery($sql)->fetchAllAssociative() as $panne) {
            $panne['distance'] = $this->geoDistance->calculerDistanceKm(
                $latTechnicien,
                $lngTechnicien,
                (float) $panne['latitude'],
                (float) $panne['longitude']
            );

            if ($panne['distance'] <= $rayonKm) {
                $pannes[] = $panne;
            }
        }

        usort($pannes, fn (array $a, array $b) => $a['distance'] <=> $b['distance']);

        return $pannes;
    }

    /**
     * Nombre de tickets par état, ex. ['ouvert' => 4, 'en_cours' => 2].
     *
     * @return array<string, int>
     */
    public function compterParEtat(): array
    {
        $lignes = $this->createQueryBuilder('r')
            ->select('r.etatTicket AS etat, COUNT(r.id) AS total')
            ->groupBy('r.etatTicket')
            ->getQuery()
            ->getArrayResult();

        return array_map('intval', array_column($lignes, 'total', 'etat'));
    }

public function findByTechnicien(Technicien $technicien): array
{
    return $this->createQueryBuilder('r')
        ->andWhere('r.technicien = :technicien')
        ->setParameter('technicien', $technicien)
        ->orderBy('r.dateCreation', 'DESC')
        ->getQuery()
        ->getResult();
}
}
