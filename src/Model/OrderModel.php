<?php

namespace App\Model;

use App\DTO\OrderDTO;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\OrderRepository;

class OrderModel
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly ProductModel $productModel,
    ) {}

    public function update(Order $order, OrderDTO $orderDTO): void
    {
        $products = $this->productModel->getProductsByOrderDTO($orderDTO);

        if ($products) {
            foreach ($products as $product) {
                $order->addProduct($product);
            }
        }

        if ($orderDTO->getStatus()) {
            $order->setStatus($orderDTO->getStatus());
        }

        $this->orderRepository->save($order, true);
    }

    public function removeProduct(Order $order, Product $product): void
    {
        $order->removeProduct($product);

        $this->orderRepository->save($order, true);
    }

    public function create(User $user): Order
    {
        $order = new Order();
        $order->setUser($user);
        $order->setStatus(Order::PROCESSING);
        $order->setCreatedAt(new \DateTimeImmutable('now'));

        $this->orderRepository->save($order, true);
        return $order;
    }

    /**
     * Find or create new order with status PROCESSING
     * @param User $user
     * @return Order
     */
    public function getProcessingOrder(User $user): Order
    {
        $orders = $user->getOrders();

        foreach ($orders as $order) {
            if ($order->getStatus() == Order::PROCESSING) {
                return $order;
            }
        }

        return $this->create($user);
    }

}