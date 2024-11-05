<?php

namespace App\DataFixtures;

use App\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CityFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i <= 20; $i++) {
            $city = new City();
            $city->setName('City#' . $i);
            $rand = rand(1, 10);
            $city->setCountry($this->getReference('Country-' . $rand));
            $manager->persist($city);
            $this->setReference('City-' . $i, $city);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CountryFixtures::class,
        ];
    }
}
