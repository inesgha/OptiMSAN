<?php

namespace App\Repository;

use App\Entity\PortMSAN;
use App\Entity\SlotCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PortMSAN>
 */
class PortMSANRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PortMSAN::class);
    }

    /**
     * Nombre de ports enregistrés sur une carte (en excluant éventuellement un port précis).
     */
    public function compterPortsSurCarte(SlotCard $carte, ?int $exclureId = null): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.slotCard = :carte')
            ->setParameter('carte', $carte);

        if (null !== $exclureId) {
            $qb->andWhere('p.id != :id')->setParameter('id', $exclureId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    //    /**
    //     * @return PortMSAN[] Returns an array of PortMSAN objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PortMSAN
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
