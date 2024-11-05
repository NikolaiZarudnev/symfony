<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Service\CartService;
use App\Service\StripeService;
use Stripe;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StripeController extends AbstractController
{
    public function __construct(
        private readonly Security        $security,
        private readonly OrderRepository $orderRepository,
        private readonly StripeService   $stripeService,
        private readonly CartService     $cartService,
    ) {}

    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $order = $this->orderRepository->findOneBy(['status' => Order::PROCESSING]);
        $amount = $order->getTotalCost();

        return $this->render('stripe/index.html.twig', [
            'amount' => $amount,
        ]);
    }

    public function refundPayment(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $orderId = $request->request->get('orderId');

        $order = $this->orderRepository->find($orderId);

        if ($order->getStatus() === Order::PAID) {
            $this->stripeService->refundPayment($order->getPayment()->getPaymentIntentId());
        }

        return new JsonResponse();
    }

    public function createPayment(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        /** @var User $user */
        $user = $this->security->getUser();

        $order = $this->cartService->getCart($request->cookies, $user);

        $amount = $order->getTotalCost();
        $email = $user->getEmail();

        $payment = $this->stripeService->createPayment(
            $amount,
            'usd',
            'Order payment',
            $email,
            $order->getId()
        );

        return new JsonResponse([
            'clientSecret' => $payment->client_secret,
            'orderId' => $order->getId()
        ]);
    }

    public function stripeNotify(Request $request)
    {
        Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET"]);

        $sig_header = $request->headers->get('Stripe-Signature');
        try {
            $this->stripeService->webHook($sig_header);
        } catch (UnexpectedValueException $e) {
            // Invalid payload
            return new Response(status: 400);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            return new Response(status: 400);
        }

        return new Response(status: 204);
    }
}
