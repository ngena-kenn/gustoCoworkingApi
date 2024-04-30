<?php

namespace App\Repository;

use App\Entity\SalonPrivee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SalonPrivee>
 *
 * @method SalonPrivee|null find($id, $lockMode = null, $lockVersion = null)
 * @method SalonPrivee|null findOneBy(array $criteria, array $orderBy = null)
 * @method SalonPrivee[]    findAll()
 * @method SalonPrivee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalonPriveeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SalonPrivee::class);
    }

    public function save(SalonPrivee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SalonPrivee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SalonPrivee[] Returns an array of SalonPrivee objects
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

//    public function findOneBySomeField($value): ?SalonPrivee
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
