<?php

namespace App\Security;

use App\Entity\Account;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountVoter extends Voter
{
    const SHOW = 'show';
    const EDIT = 'edit';
    const DELETE = 'delete';

    public function __construct(
        private Security $security,
        private readonly TranslatorInterface $translator,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::SHOW, self::EDIT, self::DELETE])) {
            return false;
        }

        if (!$subject instanceof Account) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($this->security->isGranted(User::ROLE_ADMIN)) {
            return true;
        }

        /** @var Account $account */
        $account = $subject;

        return match ($attribute) {
            self::SHOW => $this->canShow($account, $user),
            self::EDIT => $this->canEdit($account, $user),
            self::DELETE => $this->canDelete($account, $user),
            default => throw new \LogicException($this->translator->trans('This code should not be reached!', domain: 'exceptions'))
        };
    }

    private function canShow(Account $account, User $user): bool
    {
        if ($this->security->isGranted(User::ROLE_MANAGER)) {
            return true;
        }

        if ($this->security->isGranted(User::ROLE_SMALL_MANAGER)) {
            return $user === $account->getOwner();
        }

        return false;
    }

    private function canEdit(Account $account, User $user): bool
    {
        if ($user === $account->getOwner()) {
            return true;
        }

        return false;
    }

    private function canDelete(Account $account, User $user): bool
    {
        if ($user === $account->getOwner()) {
            return true;
        }

        return false;
    }
}