<?php

namespace App\DataFixtures;

use App\Entity\Order;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference('User-0');

        $order = new Order();
        $order->addProduct($this->getReference('Product-' . rand(0, 49)));
        $order->addProduct($this->getReference('Product-' . rand(0, 49)));
        $order->addProduct($this->getReference('Product-' . rand(0, 49)));
        $order->setStatus(Order::PROCESSING);
        $order->setCreatedAt(new \DateTimeImmutable('now'));

        $user->addOrder($order);

        $manager->persist($order);

        $yearAgo = new \DateTimeImmutable('-1 year');
        $now = new \DateTimeImmutable('now');

        for ($i = 0; $i < 100; $i++) {
            $user = $this->getReference('Role_User-' . rand(0, 49));

            $order = new Order();
            $order->setStatus(Order::PAID);

            $randTimeStamp = rand($yearAgo->getTimestamp(), $now->getTimestamp());
            $order->setCreatedAt((new \DateTimeImmutable())->setTimestamp($randTimeStamp));

            $count = rand(1, 10);
            for ($j = 0; $j < $count; $j++) {
                $order->addProduct($this->getReference('Product-' . rand(0, 49)));
            }

            $user->addOrder($order);

            $manager->persist($order);
            $manager->persist($user);
        }

        $order = new Order();
        $order->addProduct($this->getReference('Product-' . rand(0, 49)));
        $order->addProduct($this->getReference('Product-' . rand(0, 49)));
        $order->addProduct($this->getReference('Product-' . rand(0, 49)));
        $order->setStatus(Order::CANCELLED);
        $order->setCreatedAt(new \DateTimeImmutable('now'));

        $user->addOrder($order);

        $manager->persist($order);
        $manager->persist($user);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ProductFixtures::class,
        ];
    }
}