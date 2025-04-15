<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }


    public function findUpcomingSorties(int $limit = 6): array
    {
        $now = new \DateTime();

        // Récupérer les sorties à l'état "Ouverte" dont la date est dans le futur
        // et qui ne sont pas complètes
        return $this->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->andWhere('e.libelle = :etat')
            ->andWhere('s.dateHeureDebut > :now')
            ->andWhere('s.nbInscriptionsMax > SIZE(s.inscriptions)')
            ->setParameter('etat', 'Ouverte')
            ->setParameter('now', $now)
            ->orderBy('s.dateHeureDebut', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
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

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
