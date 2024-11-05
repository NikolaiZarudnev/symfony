<?php

namespace App\EventSubscriber;

use App\Event\AccountChangedEvent;
use App\Service\AccountCacher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AccountSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack   $requestStack,
        private readonly AccountCacher  $accountCacher,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AccountChangedEvent::NAME => 'onChangedAccount',
        ];
    }

    public function onChangedAccount(AccountChangedEvent $event): void
    {
        if ($event->getAccount()->getId()) {
            $session = $this->requestStack->getSession();
            $time = new \DateTimeImmutable('now');

            $session->set('changedDate', $time->format('d-m-Y H:m:s'));
            $session->set('accountChangedId', $event->getAccount()->getId());

            $this->accountCacher->setAccount($event->getAccount());
        }
    }

}