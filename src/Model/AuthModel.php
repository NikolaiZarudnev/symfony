<?php

namespace App\Model;

use App\Entity\AuthMail;
use App\Entity\User;
use App\Repository\AuthMailRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthModel
{
    public function __construct(
        private readonly AuthMailRepository $authMailerRepository,
        private readonly ContainerInterface $container,
        private readonly UserModel          $userModel,
    )
    {
    }

    public function create(User $user, string $route): AuthMail
    {
        $code = uniqid();
        $link = $this->container->get('router')->generate($route, ['code' => $code], UrlGeneratorInterface::ABSOLUTE_PATH);

        $authMail = new AuthMail();
        $authMail->setUser($user);
        $authMail->setCode($code);
        $authMail->setLink('http://test.loc' . $link);
        $authMail->setExpirationDate(new \DateTimeImmutable('now +1 days'));

        $this->authMailerRepository->save($authMail, true);

        return $authMail;
    }

    public function delete(AuthMail $authMail): void
    {
        $this->authMailerRepository->remove($authMail, true);
    }

    public function activateUser(AuthMail $authMail): void
    {
        if ($this->checkExpirationDate($authMail->getExpirationDate())) {
            $this->userModel->update($authMail->getUser(), ['isActive' => true]);
        }
    }

    public function recoverPassword(AuthMail $authMail, $newPassword): void
    {
        if ($this->checkExpirationDate($authMail->getExpirationDate())) {
            $this->userModel->update($authMail->getUser(), ['password' => $newPassword]);
        }
    }

    private function checkExpirationDate(\DateTimeImmutable $targetDate): bool
    {
        return date_timestamp_get($targetDate) - date_timestamp_get(new \DateTimeImmutable('now')) > 0;
    }
}