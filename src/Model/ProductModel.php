<?php

namespace App\Model;

use App\DTO\OrderDTO;
use App\Entity\Product;
use App\Repository\ProductRepository;

class ProductModel
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {}

    /**
     * @param OrderDTO $orderDTO
     * @return Product[]
     */
    public function getProductsByOrderDTO(OrderDTO $orderDTO): array
    {
        $ids = [];
        if ($orderDTO->getProductsDTO()) {
            foreach ($orderDTO->getProductsDTO() as $productDTO) {
                $ids[] = $productDTO->getId();
            }
        }

        return $this->productRepository->findByArray('id', $ids);
    }
}