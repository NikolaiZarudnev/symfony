<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class ProductDTO
{
    public function __construct(
        private ?int $id = null,
        private ?string $name = null,
        private ?float $cost = null,
    ) {}

    /**
     * @return int|null
     */
    #[Groups(['orderDTO'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return ProductDTO
     */
    #[Groups(['orderDTO'])]
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getCost(): ?float
    {
        return $this->cost;
    }

    /**
     * @param float|null $cost
     */
    public function setCost(?float $cost): self
    {
        $this->cost = $cost;

        return  $this;
    }
}