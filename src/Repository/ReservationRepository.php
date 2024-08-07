<?php

namespace App\Repository;

use App\Entity\Vehicule;
use App\Entity\Reservation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function isVehiculeReserved(Vehicule $vehicule, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): bool
    {
        $queryBuilder = $this->createQueryBuilder('r');

        $queryBuilder->select('count(r.id)')
            ->where('r.vehicule = :vehicule')
            ->andWhere('r.dateDebut < :dateFin')
            ->andWhere('r.dateFin > :dateDebut')
            ->setParameter('vehicule', $vehicule)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin);

        // On vérifie si le résultat est supérieur à zéro (le véhicule est déjà réservé)
        return (int) $queryBuilder->getQuery()->getSingleScalarResult() > 0;
    }

    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
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

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
