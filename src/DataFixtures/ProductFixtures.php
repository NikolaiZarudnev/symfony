<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 50; $i++) {
            $product = new Product();
            $product->setName('ProductName' . $i);
            $product->setCost(rand(50, 1000));

            $manager->persist($product);
            $this->setReference('Product-' . $i, $product);
        }

        $manager->flush();
    }
}