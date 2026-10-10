<?php

namespace App\Repository;

use App\Entity\DemandeLigne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandeLigne>
 */
class DemandeLigneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeLigne::class);
    }

    //    /**
    //     * @return DemandeLigne[] Returns an array of DemandeLigne objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DemandeLigne
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * Nombre de demandes par état, ex. ['en_attente' => 3, 'installee' => 1].
     *
     * @return array<string, int>
     */
    public function compterParEtat(): array
    {
        $lignes = $this->createQueryBuilder('d')
            ->select('d.etatDemande AS etat, COUNT(d.id) AS total')
            ->groupBy('d.etatDemande')
            ->getQuery()
            ->getArrayResult();

        return array_map('intval', array_column($lignes, 'total', 'etat'));
    }

    public function findEnAttentePourCarte(): array
{
    return $this->createQueryBuilder('d')
        ->select('d.id', 'd.codeDemande', 'd.latitude', 'd.longitude', 'd.typeOffre', 'd.adresseInstallation')
        ->andWhere('d.etatDemande = :etat')
        ->andWhere('d.latitude IS NOT NULL')
        ->andWhere('d.longitude IS NOT NULL')
        ->setParameter('etat', 'en_attente')
        ->getQuery()
        ->getResult();
} 
}
