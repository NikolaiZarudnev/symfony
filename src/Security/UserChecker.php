<?php

namespace App\Security;

use App\Entity\User as AppUser;
use App\Service\CartService;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly CartService $cartService,
    ) {}
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        if (!$user->getIsActive()) {
            throw new CustomUserMessageAccountStatusException($this->translator->trans('User account is not activated.', domain: 'exceptions'));
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        $this->cartService->uniteProcessingOrder($user);
    }


}