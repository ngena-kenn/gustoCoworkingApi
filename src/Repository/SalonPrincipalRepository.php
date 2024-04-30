<?php

namespace App\Repository;

use App\Entity\SalonPrincipal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SalonPrincipal>
 *
 * @method SalonPrincipal|null find($id, $lockMode = null, $lockVersion = null)
 * @method SalonPrincipal|null findOneBy(array $criteria, array $orderBy = null)
 * @method SalonPrincipal[]    findAll()
 * @method SalonPrincipal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalonPrincipalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SalonPrincipal::class);
    }

    public function save(SalonPrincipal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SalonPrincipal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SalonPrincipal[] Returns an array of SalonPrincipal objects
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

//    public function findOneBySomeField($value): ?SalonPrincipal
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
