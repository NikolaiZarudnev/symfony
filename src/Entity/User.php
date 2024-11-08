<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Index(columns: ['email'], name: 'search_index', flags: ['fulltext'])]
#[UniqueEntity(fields: ['email'], message: 'forms.constraints.email.unique')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_MANAGER = 'ROLE_MANAGER';
    public const ROLE_SMALL_MANAGER = 'ROLE_SMALL_MANAGER';
    public const ROLE_USER = 'ROLE_USER';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user'])]
    private ?int $id = null;


    #[Groups(['user'])]
    private ?int $searchIndex = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['user'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Account::class)]
    #[MaxDepth(2)]
    private Collection $accounts;

    #[ORM\Column(nullable: true)]
    #[Groups(['user'])]
    private ?\DateTimeImmutable $loggedAt = null;

    #[ORM\Column]
    #[Groups(['user'])]
    private bool $isActive = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: AuthMail::class, cascade: ['persist'], orphanRemoval: true)]
    #[MaxDepth(2)]
    private Collection $authMails;

    #[ORM\Column(nullable: true)]
    #[Groups(['user'])]
    private ?string $googleId;
    #[ORM\Column(nullable: true)]
    #[Groups(['user'])]
    private ?string $hostedDomain;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Order::class, orphanRemoval: true)]
    private Collection $orders;

    public function __construct()
    {
        $this->accounts = new ArrayCollection();
        $this->authMails = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }
    public function __toString() {
        return $this->email;
    }
    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        $roles = array_filter($roles);
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        $roles = array_filter($roles);
        $this->roles = array_unique($roles);

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Account>
     */
    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    public function addAccount(Account $account): static
    {
        if (!$this->accounts->contains($account)) {
            $this->accounts->add($account);
            $account->setOwner($this);
        }

        return $this;
    }

    public function removeAccount(Account $account): static
    {
        if ($this->accounts->removeElement($account)) {
            // set the owning side to null (unless already changed)
            if ($account->getOwner() === $this) {
                $account->setOwner(null);
            }
        }

        return $this;
    }

    public function getLoggedAt(): ?\DateTimeImmutable
    {
        return $this->loggedAt;
    }

    public function setLoggedAt(?\DateTimeImmutable $loggedAt): static
    {
        $this->loggedAt = $loggedAt;

        return $this;
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
     * @return Collection
     */
    public function getAuthMails(): Collection
    {
        return $this->authMails;
    }

    public function addAuthMail(AuthMail $authMail): static
    {
        if (!$this->authMails->contains($authMail)) {
            $this->authMails->add($authMail);
            $authMail->setUser($this);
        }

        return $this;
    }

    public function removeAuthMail(AuthMail $authMail): static
    {
        if ($this->authMails->removeElement($authMail)) {
            // set the owning side to null (unless already changed)
            if ($authMail->getUser() === $this) {
                $authMail->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    /**
     * @param string|null $googleId
     */
    public function setGoogleId(?string $googleId): void
    {
        $this->googleId = $googleId;
    }

    /**
     * @return string|null
     */
    public function getHostedDomain(): ?string
    {
        return $this->hostedDomain;
    }

    /**
     * @param string|null $hostedDomain
     */
    public function setHostedDomain(?string $hostedDomain): void
    {
        $this->hostedDomain = $hostedDomain;
    }

    /**
     * @return int|null
     */
    public function getSearchIndex(): ?int
    {
        return $this->searchIndex;
    }

    /**
     * @param int|null $searchIndex
     */
    public function setSearchIndex(?int $searchIndex): void
    {
        $this->searchIndex = $searchIndex;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }


}
