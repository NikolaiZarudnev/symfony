<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
class Account
{
    public const ACCOUNT_SEX_MALE = 1;
    public const ACCOUNT_SEX_FEMALE = 2;
    public const ACCOUNT_SEX_NOT_FOUND = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['account'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['account'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['account'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['account'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['account'])]
    private ?string $companyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['account'])]
    private ?string $position = null;

    #[ORM\Column]
    #[Groups(['account'])]
    private ?int $sex = null;

    #[ORM\OneToOne(inversedBy: 'account', targetEntity: Address::class, cascade: ['persist', 'remove'])]
    #[MaxDepth(2)]
    #[Groups(['account'])]
    private ?Address $address = null;

    #[ORM\Column(name: 'created_at')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['account'])]
    private \DateTime $createdAt;

    #[ORM\Column(name: 'updated_at')]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups(['account'])]
    private \DateTime $updatedAt;

    #[ORM\Column(name: 'deleted_at', nullable: true)]
    #[Groups(['account'])]
    private ?\DateTimeImmutable $deletedAt = null;
    #[ORM\OneToMany(mappedBy: 'account', targetEntity: Phone::class, cascade: ['persist', 'remove'])]
    #[Groups(['account'])]
    private Collection $phones;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['account'])]
    private ?string $image = null;

    #[ORM\Column(length: 128, unique: true)]
    #[Gedmo\Slug(fields: ['firstName', 'lastName'])]
    #[Groups(['account'])]
    private string $slug;

    #[ORM\ManyToOne(inversedBy: 'accounts')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['account'])]
    private ?User $owner = null;

    public function __construct()
    {
        $this->phones = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getSex(): ?int
    {
        return $this->sex;
    }

    public function setSex($sex): static
    {
        $this->sex = $sex;
        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Phone>
     */
    public function getPhones(): Collection
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone): static
    {
        if (!$this->phones->contains($phone)) {
            $phone->setAccount($this);
            $this->phones->add($phone);

        }

        return $this;
    }

    public function removePhone(Phone $phone): static
    {
        if ($this->phones->removeElement($phone)) {
            if ($phone->getAccount() === $this) {
                $phone->setAccount(null);
            }
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDeletedAt(): \DateTimeImmutable
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTimeImmutable $deletedAt
     */
    public function setDeletedAt(\DateTimeImmutable $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function getOwner(): ?user
    {
        return $this->owner;
    }

    public function setOwner(?user $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
