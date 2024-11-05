<?php

namespace App\Event;

use App\Entity\Account;
use Symfony\Contracts\EventDispatcher\Event;

class AccountChangedEvent extends Event
{
    public const NAME = 'account.changed';

    public function __construct(
        protected Account $account,
        protected string  $action,
    )
    {
    }
    /**
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

}