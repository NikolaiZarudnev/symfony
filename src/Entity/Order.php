<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    const PAID = 1;
    const CANCELLED = 2;
    const PROCESSING = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    #[Groups(['order'])]
    private ?int $status = null;

    #[ORM\Column(nullable: false)]
    #[Groups(['order'])]
    private int $totalCost = 0;

    #[ORM\OneToOne(inversedBy: 'userOrder', cascade: ['persist', 'remove'])]
    private ?Payment $payment = null;

    #[ORM\Column]
    #[Groups(['order'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'orders')]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getTotalCost(): int
    {
        return $this->totalCost;
    }

    public function setTotalCost(int $totalCost): static
    {
        $this->totalCost = $totalCost;

        return $this;
    }

    public function addCost(int $cost): static
    {
        $this->setTotalCost($this->getTotalCost() + $cost);

        return $this;
    }

    public function subCost(int $cost): static
    {
        $this->setTotalCost($this->getTotalCost() - $cost);

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): static
    {
        $this->payment = $payment;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $this->addCost($product->getCost());
            $product->addOrder($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        $this->products->removeElement($product);
        $this->subCost($product->getCost());
        $product->removeOrder($this);

        return $this;
    }
}
