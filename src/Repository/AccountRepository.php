<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\User;
use App\Form\Objects\SearchObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Account>
 *
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    public function save(Account $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Account $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findBySearch(SearchObject $search, ?User $user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->addSelect('ad')
            ->addSelect('c')
            ->leftJoin('a.address', 'ad')
            ->leftJoin('ad.country', 'c');

        if ($user) {
            $qb
                ->andWhere('a.owner = :user')
                ->setParameter('user', $user);
        }
        if ($search->getCountry()) {
            $qb
                ->andWhere('c.name LIKE :value_country')
                ->setParameter('value_country', '%' . $search->getCountry() . '%');
        }
        if ($search->getAddress()) {
            $qb
                ->andWhere('ad.street1 LIKE :value_street OR ad.street2 LIKE :value_street')
                ->setParameter('value_street', '%' . $search->getAddress() . '%');
        }
        if ($search->getFirstName()) {
            $qb
                ->andWhere('a.firstName LIKE :value1')
                ->setParameter('value1', '%' . $search->getFirstName() . '%');
        }
        if ($search->getLastName()) {
            $qb
                ->andWhere('a.lastName LIKE :value2')
                ->setParameter('value2', '%' . $search->getLastName() . '%');
        }
        if ($search->getEmail()) {
            $qb
                ->andWhere('a.email LIKE :value3')
                ->setParameter('value3', '%' . $search->getEmail() . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function getBuilder(\Doctrine\ORM\QueryBuilder $qb)
    {
        $qb
            ->select('account')
            ->addSelect('address')
            ->addSelect('country')
            ->addSelect('city')

            ->from(Account::class, 'account')
            ->leftJoin('account.address', 'address')
            ->leftJoin('address.country', 'country')
            ->leftJoin('address.city', 'city')
        ;
        return $qb;
    }

    public function getGlobalSearchBuilder(\Doctrine\ORM\QueryBuilder $qb, string $search)
    {
        $qb
            ->addSelect('address')
            ->addSelect('country')
            ->addSelect('city')
            ->leftJoin('account.address', 'address')
            ->leftJoin('address.country', 'country')
            ->leftJoin('address.city', 'city')
            ->orWhere('account.firstName LIKE :value')
            ->orWhere('account.lastName LIKE :value')
            ->orWhere('account.email LIKE :value')
            ->orWhere('account.companyName LIKE :value')
            ->orWhere('account.position LIKE :value')
            ->orWhere('account.sex LIKE :value')
            ->orWhere('country.name LIKE :value')
            ->orWhere('city.name LIKE :value')
            ->orWhere('address.street1 LIKE :value')
            ->orWhere('address.street2 LIKE :value')
            ->andWhere('account.deletedAt IS NULL')
            ->setParameter('value', '%' . $search . '%');

        $dateMin = null;
        try {
            $dateMin = new \DateTime($search);
        } catch (\Exception $e) {
        }
        if ($dateMin) {
            $dateMax = (clone $dateMin)->modify('+1 day -1 seconds');

            $qb
                ->orWhere('account.createdAt BETWEEN :dateMin AND :dateMax')
                ->setParameter('dateMin', $dateMin->format('Y-m-d H:i:s'))
                ->setParameter('dateMax', $dateMax->format('Y-m-d H:i:s'));
        }

        return $qb;
    }

    public function findByOwnerRole(SearchObject $search, ?string $role)
    {
        $qb = $this->createQueryBuilder('a');
        if ($role) {
            $qb
                ->from(User::class, 'u')
                ->andWhere('u.roles IN (:value_role)')
                ->andWhere('a.owner = u.id')
                ->setParameter('value_role', '%' . $role . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Account|null Returns Account object
     */
    public function findOneBySlug($slug): Account|null
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @return int Returns count of Account objects
     */
    public function getCountAccounts()
    {
        return $this->createQueryBuilder('a')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
//    /**
//     * @return Account[] Returns an array of Account objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Account
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
