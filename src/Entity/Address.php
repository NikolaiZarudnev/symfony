<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['account'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['account'])]
    private ?string $street1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['account'])]
    private ?string $street2 = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    #[MaxDepth(2)]
    #[Groups(['account'])]
    private ?Country $country = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    #[MaxDepth(2)]
    #[Groups(['account'])]
    private ?City $city = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['account'])]
    private ?int $zip = null;

    #[ORM\OneToOne(mappedBy: 'address', targetEntity: Account::class,cascade: ['persist'])]
    #[MaxDepth(2)]
    private ?Account $account = null;

    public function __toString()
    {
        return $this->street1 . ', ' . $this->street2 . ', ' . $this->zip;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStreet1(): ?string
    {
        return $this->street1;
    }

    public function setStreet1(?string $street1): static
    {
        $this->street1 = $street1;

        return $this;
    }

    public function getStreet2(): ?string
    {
        return $this->street2;
    }

    public function setStreet2(?string $street2): static
    {
        $this->street2 = $street2;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): static
    {
        // set the owning side of the relation if necessary
        if ($account->getAddress() !== $this) {
            $account->setAddress($this);
        }

        $this->account = $account;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getZip(): ?int
    {
        return $this->zip;
    }

    public function setZip(?int $zip): static
    {
        $this->zip = $zip;

        return $this;
    }
}
