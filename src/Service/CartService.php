<?php

namespace App\Service;

use App\DTO\OrderDTO;
use App\DTO\ProductDTO;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Model\OrderModel;
use App\Model\ProductModel;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;

class CartService
{
    public function __construct(
        private readonly OrderModel          $orderModel,
        private readonly ProductModel        $productModel,
        private readonly SerializerInterface $serializer,
        private readonly RequestStack        $requestStack,
    ) {}

    /**
     * Return Order - processing order of the user.
     *
     * Or return OrderDTO - order from cookies, if user is null
     *
     * @param InputBag $cookies
     * @param User|null $user
     * @param bool $checkProducts if true - check products in database
     * @return OrderDTO|Order|null
     */
    public function getCart(InputBag $cookies, ?User $user = null, bool $checkProducts = false): OrderDTO|Order|null
    {
        if ($user) {
            return $this->orderModel->getProcessingOrder($user);
        }

        $orderDTO = $this->getOrderDTOFromCookie($cookies);

        if ($checkProducts) {
            $products = $this->productModel->getProductsByOrderDTO($orderDTO);

            if ($products) {
                $orderDTO = $this->updateCart($orderDTO, $products);
            }
        }

        return $orderDTO;
    }

    /**
     * Unite order from cookie and processing order of the user
     *
     * @param User $user
     * @return void
     */
    public function uniteProcessingOrder(User $user): void
    {
        $orderDTO = $this->getOrderDTOFromCookie($this->requestStack->getCurrentRequest()->cookies);

        if ($orderDTO->getProductsDTO()) {
            $processOrder = $this->orderModel->getProcessingOrder($user);

            $this->orderModel->update($processOrder, $orderDTO);
        }
    }

    public function getOrderDTOFromCookie($cookies): OrderDTO
    {
        $orderJson = $cookies->get('order');

        if ($orderJson) {
            $orderDTO = $this->serializer->deserialize($orderJson, OrderDTO::class, 'json', ['groups' => ['orderDTO']]);
        } else {
            $orderDTO = new OrderDTO();
            $orderDTO->setStatus(Order::PROCESSING);
        }

        return $orderDTO;
    }

    private function updateCart(OrderDTO $orderDTO, array $products): OrderDTO
    {
        $productsDTO = [];
        foreach ($products as $product) {
            $productsDTO[] = new ProductDTO($product->getId());
        }
        $orderDTO->setProductsDTO($productsDTO);

        return $orderDTO;
    }
}