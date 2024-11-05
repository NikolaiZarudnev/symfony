<?php

namespace App\Event;

use App\Entity\AuthMail;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserAuthMailEvent extends Event
{
    public const NAME = 'user.changed';

    public function __construct(
        protected User|null     $user,
        protected AuthMail|null $authMail,
        protected string        $action,
        protected               $params = [],
    ) {}

    /**
     * @return ?User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return ?AuthMail
     */
    public function getAuthMail(): ?AuthMail
    {
        return $this->authMail;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}