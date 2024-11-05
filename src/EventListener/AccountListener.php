<?php

namespace App\EventListener;

use App\Event\AccountChangedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AccountListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
    ) {}

    #[AsEventListener(event: AccountChangedEvent::NAME)]
    public function onCreateAccountFlashMessages(AccountChangedEvent $event): void
    {
        if ($event->getAction() === 'create') {
            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add("notice", $this->translator->trans('notify.account created', domain: 'messages'));
        }
    }

    #[AsEventListener(event: AccountChangedEvent::NAME)]
    public function onUpdateAccountFlashMessages(AccountChangedEvent $event): void
    {
        if ($event->getAction() === 'update') {
            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add("notice", $this->translator->trans('notify.account updated', domain: 'messages'));
        }
    }

    #[AsEventListener(event: AccountChangedEvent::NAME)]
    public function onDeletedAccount(AccountChangedEvent $event): void
    {
        if ($event->getAction() === 'delete') {
            $session = $this->requestStack->getSession();

            $session->getFlashBag()->add(
                "notice",
                $this->translator->trans('notify.account', domain: 'messages')
                . '#'
                . $event->getAccount()->getId()
                . $this->translator->trans('notify.deleted at', domain: 'messages')
                . $event->getAccount()->getDeletedAt()->format('d-m-Y H:m:s')
            );
        }
    }
}