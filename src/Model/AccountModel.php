<?php

namespace App\Model;

use App\Entity\Account;
use App\Event\AccountChangedEvent;
use App\Repository\AccountRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AccountModel
{
    public function __construct(
        private readonly AccountRepository        $accountRepository,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly Security                 $security,
    ) {}

    public function create(Account $account, array $params): void
    {
        if (!is_null($params['image'])) {
            $account->setImage($params['image']);
        }
        $account->setOwner($this->security->getUser());
        $this->accountRepository->save($account, true);

        $event = new AccountChangedEvent($account, 'create');
        $this->dispatcher->dispatch($event, AccountChangedEvent::NAME);
    }

    public function update(Account $account, array $params): void
    {
        $account->getAddress()->setStreet1($params['streetExploded'][0]);
        $account->getAddress()->setStreet2($params['streetExploded'][1]);

        if (!is_null($params['image'])) {
            $account->setImage($params['image']);
        }
        $this->accountRepository->save($account, true);

        $event = new AccountChangedEvent($account, 'update');
        $this->dispatcher->dispatch($event, AccountChangedEvent::NAME);
    }

    public function delete(Account $account): void
    {
        $this->accountRepository->remove($account, true);

        $event = new AccountChangedEvent($account, 'delete');
        $this->dispatcher->dispatch($event, AccountChangedEvent::NAME);
    }
}