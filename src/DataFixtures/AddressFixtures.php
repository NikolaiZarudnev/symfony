<?php

namespace App\DataFixtures;

use App\Entity\Address;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AddressFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i <= 20; $i++) {
            $address = new Address();
            $address->setStreet1("Street_" . $i);
            $address->setStreet2($i + 10);
            $rand = rand(1, 20);
            $address->setCity($this->getReference('City-' . $rand));
            $address->setCountry($address->getCity()->getCountry());
            $address->setZip($i + 10000);
            $manager->persist($address);
            $this->setReference('Address-' . $i, $address);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CountryFixtures::class,
            CityFixtures::class
        ];
    }
}
