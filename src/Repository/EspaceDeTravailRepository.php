<?php

namespace App\Repository;

use App\Entity\EspaceDeTravail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EspaceDeTravail>
 *
 * @method EspaceDeTravail|null find($id, $lockMode = null, $lockVersion = null)
 * @method EspaceDeTravail|null findOneBy(array $criteria, array $orderBy = null)
 * @method EspaceDeTravail[]    findAll()
 * @method EspaceDeTravail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EspaceDeTravailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EspaceDeTravail::class);
    }

    public function save(EspaceDeTravail $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EspaceDeTravail $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return EspaceDeTravail[] Returns an array of EspaceDeTravail objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EspaceDeTravail
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
