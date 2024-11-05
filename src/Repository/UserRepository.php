<?php

namespace App\Repository;

use App\DTO\UserDTO;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    public function findLastUsers(int $count): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult();
    }

    /* @return Query Returns query
     */
    public function findAllQuery(): Query
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->getQuery();
    }

    /**
     * @return int Returns count of Users
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getCountUsers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('count(u)')
            ->getQuery()
            ->getSingleScalarResult();
    }


    /**
     * @param UserDTO $userDTO
     * @return float|int|mixed|string
     */
    public function findByUserDTO(UserDTO $userDTO): mixed
    {
        $qb = $this->createQueryBuilder('u');

        if ($userDTO->getEmail()) {
            $qb
                ->andWhere('u.email LIKE :email')
                ->setParameter('email', '%' . $userDTO->getEmail() . '%');
        }
        if ($userDTO->getRoles()) {
            $qb
                ->andWhere('JSON_EXTRACT(u.roles, \'$[0]\') LIKE :role')
                ->setParameter('role', '%' . $userDTO->getRoles()[0] . '%');
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $search
     * @return float|int|mixed|string
     */
    public function findBySearch(string $search): mixed
    {
        $qb = $this->createQueryBuilder('u');
        $qb
            ->select('NEW '.UserDTO::class.'(u.id, u.email)')
            ->andWhere('MATCH (u.email) AGAINST (\'' . $search . '*\' IN BOOLEAN MODE) > 0')
            ->orderBy('MATCH (u.email) AGAINST (\'' . $search . '*\' IN BOOLEAN MODE)')
        ;

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
