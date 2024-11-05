<?php

namespace App\Model;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserModel
{
    public function __construct(
        private readonly UserRepository      $userRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public function create(User $user, $params = []): void
    {
        foreach ($params as $key => $value) {
            match ($key) {
                'email' => $user->setEmail($value),
                'password' => $user->setPassword($this->userPasswordHasher->hashPassword(
                    $user,
                    $value)),
                'role' => $user->setRoles([$value]),
                'isActive' => $user->setIsActive($value),
            };
        }
        $this->userRepository->save($user, true);
    }

    public function createByDTO(User $user, UserDTO $userDTO): void
    {
        $user->setEmail($userDTO->getEmail());
        $user->setPassword($this->userPasswordHasher->hashPassword(
            $user,
            $userDTO->getPassword()));
        $user->setRoles($userDTO->getRoles());
        $user->setIsActive($userDTO->getIsActive());

        $this->userRepository->save($user, true);
    }

    public function update(User $user, $params = []): void
    {
        foreach ($params as $key => $value) {
            match ($key) {
                'email' => $user->setEmail($value),
                'password' => $user->setPassword($this->userPasswordHasher->hashPassword(
                    $user,
                    $value)),
                'role' => $user->setRoles([$value]),
                'isActive' => $user->setIsActive($value),
            };
        }
        $this->userRepository->save($user, true);
    }

    public function delete(User $user): void
    {
        $this->userRepository->remove($user, true);
    }


}