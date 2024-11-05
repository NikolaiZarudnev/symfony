<?php

namespace App\DTO;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Account;
use App\Entity\Address;
use App\Entity\Phone;
use App\Entity\User;
class AccountDTO
{
    public const ACCOUNT_SEX_MALE = 1;
    public const ACCOUNT_SEX_FEMALE = 2;
    public const ACCOUNT_SEX_NOT_FOUND = 3;
     
    private ?int $id = null;

    private ?string $firstName = null;

    private ?string $lastName = null;

    private ?string $email = null;

    private ?string $companyName = null;
     
    private ?string $position = null;

    private ?int $sex = null;
     
    private ?Address $address = null;

    private \DateTime $createdAt;

    private \DateTime $updatedAt;

    private ?\DateTimeImmutable $deletedAt = null;
     
    private Collection $phones;

    private ?string $image = null;

    private string $slug;
     
    private ?User $owner = null;

    public function __construct()
    {
        $this->phones = new ArrayCollection();
    }

    public function setAccountDTO(Account $account)
    {
        $this->id = $account->getId();
        $this->firstName = $account->getFirstName();
        $this->lastName = $account->getLastName();
        $this->email = $account->getEmail();
        $this->companyName = $account->getCompanyName();
        $this->position = $account->getPosition();
        $this->sex = $account->getSex();
        $this->address = $account->getAddress();
        $this->createdAt = $account->getCreatedAt();
        $this->updatedAt = $account->getUpdatedAt();
        $this->phones = $account->getPhones();
        $this->image = $account->getImage();
        $this->slug = $account->getSlug();
        $this->owner = $account->getOwner();
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
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
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
