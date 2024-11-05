<?php

namespace App\DTO;

use App\Entity\Product;
use Symfony\Component\Serializer\Annotation\Groups;

class OrderDTO
{
    #[Groups(['orderDTO'])]
    private ?int $id = null;

    private ?UserDTO $userDTO = null;

    /**
     * @var ProductDTO[]
     */
    #[Groups(['orderDTO'])]
    private ?array $productsDTO = null;

    private ?int $status = null;

    private ?float $totalCost = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserDTO(): ?UserDTO
    {
        return $this->userDTO;
    }

    public function setUserDTO(?UserDTO $userDTO): static
    {
        $this->userDTO = $userDTO;

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

    /**
     * @param ProductDTO[] $productsDTO
     * @return OrderDTO
     */
    public function setProductsDTO(?array $productsDTO): static
    {
        $this->productsDTO = $productsDTO;

        return $this;
    }

    /**
     * @return ProductDTO[]
     */
    public function getProductsDTO(): ?array
    {
        return $this->productsDTO;
    }
//    /**
//     * @param ProductDTO[] $productsDTO
//     * @return OrderDTO
//     */
//    public function setProducts(?array $productsDTO): static
//    {
//        $this->productsDTO = $productsDTO;
//
//        return $this;
//    }
//
//    /**
//     * @return ProductDTO[]
//     */
//    public function getProducts(): ?array
//    {
//        return $this->productsDTO;
//    }

    public function addProduct(ProductDTO $product): static
    {
        if(!$this->productsDTO || !$this->searchProduct($product)) {
            $this->productsDTO[] = $product;
        }

        return $this;
    }
    public function removeProduct(ProductDTO $product): static
    {
        if ($key = $this->searchProduct($product)) {
            unset($this->productsDTO[$key]);
        }

        return $this;
    }
    /**
     * @return float|null
     */
    public function getTotalCost(): ?float
    {
        $this->totalCost = 0;

        if ($this->productsDTO) {
            /** @var ProductDTO $productDTO */
            foreach ($this->productsDTO as $product) {
                $this->totalCost += $product->getCost();
            }
        }

        return $this->totalCost;
    }

    /**
     * @param float|null $totalCost
     */
    public function setTotalCost(?float $totalCost): void
    {
        $this->totalCost = $totalCost;
    }

    public function searchProduct(ProductDTO $needleProduct): bool|int|null
    {
        foreach ($this->productsDTO as $key => $productDTO) {
            if ($productDTO->getId() === $needleProduct->getId()) {
                return $key;
            }
        }

        return false;
    }
}
