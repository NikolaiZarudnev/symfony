<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class UserDTO
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_MANAGER = 'ROLE_MANAGER';
    public const ROLE_SMALL_MANAGER = 'ROLE_SMALL_MANAGER';
    public const ROLE_USER = 'ROLE_USER';

    #[Groups(['userDTO'])]
    private ?int $id = null;

    #[Groups(['userDTO'])]
    private ?string $email = null;

    private array $roles = [];

    private ?string $password = null;

    private bool $isActive = false;

    private ?OrderDTO $order;
    public function __construct(?int $id, string $email)
    {
        $this->id = $id;
        $this->email = $email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles = array_filter($roles);
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $roles = array_filter($roles);
        $this->roles = array_unique($roles);

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return ?OrderDTO
     */
    public function getOrder(): ?OrderDTO
    {
        return $this->order;
    }

    /**
     * @param ?OrderDTO $order
     */
    public function setOrder(?OrderDTO $order): void
    {
        $this->order = $order;
    }
}
