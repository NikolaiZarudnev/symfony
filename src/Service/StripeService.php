<?php

namespace App\Service;

use Stripe;
use Stripe\Event as StripeEvent;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\StripeClient;
use Stripe\Webhook as StripeWebhook;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class StripeService
{
    private StripeClient $stripe;

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
    )
    {
        $this->stripe = new StripeClient($_ENV["STRIPE_SECRET"]);
        Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET"]);
    }

    public function createPayment(int $amount, string $currency, string $description, string $email, int $orderId): PaymentIntent
    {
        return $this->stripe->paymentIntents->create([
            'amount' => $amount,
            'currency' => $currency,
            'automatic_payment_methods' => ['enabled' => true],
            "description" => $description,
            'payment_method_options[card][request_three_d_secure]' => 'any',
            'metadata' => ['order_id' => $orderId],
        ]);
    }

    public function refundPayment(string $paymentIntentId): Refund
    {
        return $this->stripe->refunds->create(['payment_intent' => $paymentIntentId]);
    }

    /**
     * @param $sig_header
     * @return void
     * @throws SignatureVerificationException
     */
    public function webHook($sig_header): void
    {
        $payload = @file_get_contents('php://input');

        $event = null;

        try {
            $event = StripeWebhook::constructEvent(
                $payload, $sig_header, 'whsec_HYSx5hM9v4I8z0TdSjFfCUStGBWoHXrL'
            );
        } catch (UnexpectedValueException $e) {
            // Invalid payload
            throw new $e;
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            throw new $e;
        }

        $this->dispatcher->dispatch($event, StripeEvent::class);
    }
}