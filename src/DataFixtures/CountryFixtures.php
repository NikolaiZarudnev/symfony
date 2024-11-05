<?php

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CountryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i <= 20; $i++) {
            $country = new Country();
            $country->setName('Country-' . $i);
            $manager->persist($country);
            $this->setReference('Country-' . $i, $country);
        }
        $manager->flush();
    }
}
