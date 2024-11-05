<?php

namespace App\EventSubscriber;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['setOrderTotalCost'],
        ];
    }

    public function setOrderTotalCost(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Order)) {
            return;
        }

        $totalCost = 0;
        foreach ($entity->getProducts() as $product) {
            $totalCost += $product->getCost();
        }

        $entity->setTotalCost($totalCost);
    }
}