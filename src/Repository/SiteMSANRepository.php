<?php

namespace App\Repository;

use App\Entity\SiteMSAN;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SiteMSAN>
 */
class SiteMSANRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteMSAN::class);
    }

//    /**
//     * @return SiteMSAN[] Returns an array of SiteMSAN objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SiteMSAN
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
public function findSaturationParSite(): array
{
    $conn = $this->getEntityManager()->getConnection();

    $sql = "
        SELECT 
            s.id,
            s.nom_site,
            s.type_msan,
            COUNT(p.id) AS total_ports,
            SUM(CASE WHEN p.etat_port = 'occupe' THEN 1 ELSE 0 END) AS ports_occupes,
            SUM(CASE WHEN p.etat_port = 'libre' THEN 1 ELSE 0 END) AS ports_libres,
            SUM(CASE WHEN p.etat_port = 'panne' THEN 1 ELSE 0 END) AS ports_en_panne
        FROM site_msan s
        LEFT JOIN shelf sh ON sh.site_msan_id = s.id
        LEFT JOIN slot_card sc ON sc.shelf_id = sh.id
        LEFT JOIN port_msan p ON p.slot_card_id = sc.id
        GROUP BY s.id, s.nom_site, s.type_msan
        ORDER BY s.nom_site ASC
    ";

    return $conn->executeQuery($sql)->fetchAllAssociative();
}
public function findAllPourCarte(): array
{
    return $this->createQueryBuilder('s')
        ->select('s.id', 's.nomSite', 's.latitude', 's.longitude', 's.typeMSAN')
        ->getQuery()
        ->getResult();
}
}
