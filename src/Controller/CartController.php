<?php

namespace App\Controller;

use App\DTO\OrderDTO;
use App\DTO\ProductDTO;
use App\Entity\Order;
use App\Entity\User;
use App\Model\OrderModel;
use App\Model\ProductModel;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class CartController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository   $productRepository,
        private readonly ProductModel   $productModel,
        private readonly OrderModel          $orderModel,
        private readonly SerializerInterface $serializer,
        private readonly CartService         $cartService,
    ) {}

    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $cart = $this->cartService->getCart($request->cookies, $user, checkProducts: true);

        if ($cart instanceof OrderDTO) {
            $products = $this->productModel->getProductsByOrderDTO($cart);

            if ($products) {
                $productsDTO = [];
                foreach ($products as $product) {
                    $productsDTO[] = new ProductDTO($product->getId(), $product->getName(), $product->getCost());
                }

                $cart->setProductsDTO($productsDTO);
            }
        }

        $response = new Response();

        if ($user) {
            $response->headers->clearCookie('order');
        }

        return $this->render('cart/index.html.twig', [
            'order' => $cart,
        ], $response);
    }

    public function apiAddProduct(Request $request): JsonResponse
    {
        $productId = $request->get('productId');
        $product = $this->productRepository->find($productId);

        $user = $this->getUser();

        $order = $this->cartService->getCart($request->cookies, $user);

        if ($user) {
            $this->orderModel->update($order, (new OrderDTO())->addProduct(new ProductDTO(
                $product->getId(),
            )));

            $response = new JsonResponse();
            $response->headers->clearCookie('order');
        } else {
            $order->addProduct(new ProductDTO(
                $product->getId(),
            ));
            $orderJson = $this->serializer->serialize($order, 'json', ['groups' => ['orderDTO']]);

            $response = new JsonResponse($orderJson, json: true);
            $response->headers->setCookie(new Cookie('order', $orderJson, time() + (5 * 86400)));
        }

        return $response;
    }

    public function apiRemoveProduct(Request $request): JsonResponse
    {
        $productId = $request->get('productId');
        $product = $this->productRepository->find($productId);

        $user = $this->getUser();

        $order = $this->cartService->getCart($request->cookies, $user);

        if ($user) {
            $this->orderModel->removeProduct($order, $product);

            $response = new JsonResponse($product->getId());
        } else {
            $order->removeProduct(new ProductDTO(
                $product->getId(),
            ));
            $orderJson = $this->serializer->serialize($order, 'json', ['groups' => ['orderDTO']]);

            $response = new JsonResponse($product->getId());
            $response->headers->setCookie(new Cookie('order', $orderJson, time() + (5 * 86400)));
        }

        return $response;
    }

    public function apiGetCountProducts(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $cart = $this->cartService->getCart($request->cookies, $user, checkProducts: true);

        if ($cart instanceof Order) {
            $countProducts = $cart->getProducts()->count();
        } elseif ($cart instanceof OrderDTO) {
            $countProducts = count($cart->getProductsDTO());
        } else {
            $countProducts = null;
        }

        return new JsonResponse($countProducts);
    }

    public function apiGetTotalCost(Request $request): Response
    {
        $user = $this->getUser();

        $order = $this->cartService->getCart($request->cookies, $user);

        $amount = $order->getTotalCost();

        return new JsonResponse($amount);
    }
}
