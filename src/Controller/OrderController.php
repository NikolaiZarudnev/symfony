<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository     $orderRepository,
        private readonly CartService         $cartService,
        private readonly SerializerInterface $serializer,
    )
    {
    }

    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        /** @var User $user */
        $user = $this->getUser();

        $orders = [];

        if ($user) {
            $orders = $this->orderRepository->findByUser($user);
        } else {
            $orderDTO = $this->cartService->getCart($request->cookies, checkProducts: true);
            if ($orderDTO !== null) {
                $orders[] = $orderDTO;
            }
        }

        $response = new Response();
        $response->headers->clearCookie('order');

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ], $response);
    }

    public function show(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        /** @var User $user */
        $user = $this->getUser();

        $order = $this->orderRepository->findOneByUser($user, $id);

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    public function apiGetStatus(Request $request): Response
    {
        $orderId = $request->request->get('orderId');

        $order = $this->orderRepository->find($orderId);

        $status = $order->getStatus();

        return new JsonResponse($status);
    }

    public function apiGetOrdersBetweenDates(Request $request): JsonResponse
    {
        $startNotation = $request->request->get('startDate');
        $endNotation = $request->request->get('endDate');
        $perInterval = $request->request->get('dataPerInterval');
        if ($perInterval === null || $perInterval === 'undefined') {
            $perInterval = 'month';
        }
        $startDate = new \DateTimeImmutable($startNotation);
        $endDate = new \DateTimeImmutable($endNotation);


        $diff = date_diff($endDate, $startDate);

        $countItems = match ($perInterval) {
            'month' => $diff->m,
            'week' => $diff->days / 7,
            'day' => $diff->days,
        };

        $format = match ($perInterval) {
            'month' => 'M',
            'week' => 'W',
            'day' => 'd',
        };

        for ($i = 0; $i <= $countItems; $i++) {
            $dataResponse['items'][$i] = $this->betweenDates(
                (clone $startDate)->modify(sprintf('+%s %s', $i, $perInterval)),
                (clone $startDate)->modify(sprintf('+%s %s -1 sec', $i+1, $perInterval)),
                $format
            );
        }

        $countProducts = $this->orderRepository->getCountSoldProductsBetweenDates($startDate, $endDate);
        $sumTotalCost = $this->orderRepository->getSumSoldTotalCostBetweenDates($startDate, $endDate);

        $dataResponse['countProducts'] = $countProducts;
        $dataResponse['sumTotalCost'] = $sumTotalCost;
        $dataResponse['interval'] = $perInterval;

        return new JsonResponse($dataResponse);
    }

    private function betweenDates(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, string $format)
    {
        $countProducts = $this->orderRepository->getCountSoldProductsBetweenDates($startDate, $endDate);
        $sumTotalCost = $this->orderRepository->getSumSoldTotalCostBetweenDates($startDate, $endDate);

        if ($format === 'W') {
            $dataResponse['labelInterval'] = sprintf('%s - %s', $startDate->format('d-m-Y'), $endDate->format('d-m-Y'));
        } else {
            $dataResponse['labelInterval'] = $startDate->format($format);
        }

        $dataResponse['countProducts'] = $countProducts;
        $dataResponse['sumTotalCost'] = $sumTotalCost;
        $dataResponse['startDate'] = $startDate;
        $dataResponse['endDate'] = $endDate;

        return $dataResponse;
    }
}
