<?php

namespace App\EventListener;

use App\Event\UserAuthMailEvent;
use App\Model\AuthModel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;


final class UserListener
{
    public function __construct(
        private readonly AuthModel $authMailModel,
    )
    {
    }

    #[AsEventListener(event: LoginSuccessEvent::class)]
    public function onAuthenticationSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $user->setLoggedAt(new \DateTimeImmutable('now'));
    }

    #[AsEventListener(event: UserAuthMailEvent::NAME)]
    public function onRecoveryPasswordChange(UserAuthMailEvent $event): void
    {
        if ($event->getAction() === 'recoverPassword') {
            $authMail = $event->getAuthMail();
            $this->authMailModel->recoverPassword($authMail, $event->getParams()['password']);
            $this->authMailModel->delete($authMail);
        }
    }

    #[AsEventListener(event: UserAuthMailEvent::NAME)]
    public function onActivateUser(UserAuthMailEvent $event): void
    {
        if ($event->getAction() === 'activateUser') {
            $this->authMailModel->activateUser($event->getAuthMail());
            $this->authMailModel->delete($event->getAuthMail());
        }
    }
}