<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $ROLES = [[User::ROLE_ADMIN], [User::ROLE_MANAGER], [User::ROLE_SMALL_MANAGER], [User::ROLE_USER]];

        for ($i = 0; $i < 4; $i++) {
            $user = new User();
            $user->setEmail('test' . $i . '@example.com');
            $password = $this->hasher->hashPassword($user, $user->getEmail());
            $user->setPassword($password);
            $user->setRoles($ROLES[$i]);
            $this->setReference('User-' . $i, $user);
            $user->setIsActive(true);

            $manager->persist($user);
        }
        $user = new User();
        $user->setEmail('admin@example.com');
        $password = $this->hasher->hashPassword($user, $user->getEmail());
        $user->setPassword($password);
        $user->setRoles([User::ROLE_ADMIN]);
        $user->setIsActive(true);
        $manager->persist($user);

        $manager->flush();

        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $user->setEmail('user-' . $i . '@example.com');
            $password = $this->hasher->hashPassword($user, $user->getEmail());
            $user->setPassword($password);
            $user->setRoles([User::ROLE_USER]);
            $this->setReference('Role_User-' . $i, $user);
            $user->setIsActive(true);

            $manager->persist($user);
        }
//        for ($i = 0; $i < 30; $i++) {
//            $user = new User();
//            $user->setEmail('user-' . $i . '@test.test');
//            $password = $this->hasher->hashPassword($user, $user->getEmail());
//            $user->setPassword($password);
//            $user->setRoles([User::ROLE_USER]);
//            $this->setReference('CommonUser-' . $i, $user);
//            $user->setIsActive(true);
//            $manager->persist($user);
//        }
    }
}