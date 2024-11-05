<?php

namespace App\DataFixtures;

use App\Entity\Account;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AccountFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $accounts = [];
        for ($i = 0; $i < 20; $i++) {
            $account = new Account();
            $account->setFirstName('FirstName' . $i);
            $account->setLastName('LastName' . $i);
            $account->setEmail('Email' . $i . '@example.com');
            $account->setCompanyName('CompanyName' . $i);
            $account->setPosition('Position' . $i);
            $account->setSex(rand(1, 3));
            $account->setAddress($this->getReference('Address-' . $i));
            $accounts[] = $account;

        }
        for ($i = 0; $i < 4; $i++) {
            for ($k = 0; $k < 5; $k++) {
                $accounts[$k + $i * 5]->setOwner($this->getReference('User-' . $i));
                $manager->persist($accounts[$k + $i * 5]);
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            AddressFixtures::class,
            UserFixtures::class,
        ];
    }
}
