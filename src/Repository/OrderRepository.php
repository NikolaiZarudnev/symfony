<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function save(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    /**
     * @return Order[] Returns an array of Order objects ordered by 'created_at' DESC
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param User $user
     * @param $id
     * @return Order|null
     * @throws NonUniqueResultException
     */
    public function findOneByUser(User $user, $id): Order|null
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @return Order[] Returns an array of Order objects ordered by 'created_at' ASC
     */
    public function findByCreatedAtBetweenDates($startDate, $endDate): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.createdAt BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('o.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return int|null Returns count of products orders between startDate and endDate
     * @throws NonUniqueResultException
     */
    public function getCountSoldProductsBetweenDates($startDate, $endDate): int|null
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(p.id)')
            ->leftJoin('o.products', 'p')
            ->andWhere('o.createdAt BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return int|null Returns sum of all total cost order's between startDate and endDate
     * @throws NonUniqueResultException
     */
    public function getSumSoldTotalCostBetweenDates($startDate, $endDate): int|null
    {
        return $this->createQueryBuilder('o')
            ->select('SUM(o.totalCost)')
            ->andWhere('o.createdAt BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
//    /**
//     * @return Order[] Returns an array of Order objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Order
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
