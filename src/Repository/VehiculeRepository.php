<?php

namespace App\Repository;

use App\Entity\Vehicule;
use App\Entity\Categorie;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Vehicule>
 */
class VehiculeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicule::class);
    }

    public function findVehiculesByCategory()
    {
    return $this->createQueryBuilder('v')
        ->select('c, m, ma, v')
        ->leftJoin('v.modele', 'm')
        ->leftJoin('m.marque', 'ma')
        ->leftJoin('v.categorie', 'c')
        ->orderBy('c.nom', 'ASC')
        ->getQuery()
        ->getResult();
    }


    public function findAllCategories()
    {
        return $this->createQueryBuilder('v')
            ->select('c')
            ->from('App\Entity\Categorie', 'c')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findImageByCategorie(Categorie $categorie)
    {
    return $this->createQueryBuilder('v')
        ->select('v.img', 'c.id AS categoryId') // Sélectionne l'image et l'ID de la catégorie
        ->leftJoin('v.categorie', 'c')
        ->where('v.categorie = :categorie')
        ->setParameter('categorie', $categorie)
        ->setMaxResults(1) // Limite le résultat à une seule image par catégorie
        ->getQuery()
        ->getOneOrNullResult();
    }

    


    //    /**
    //     * @return Vehicule[] Returns an array of Vehicule objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vehicule
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
