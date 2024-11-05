<?php

namespace App\EventListener;

use App\DTO\OrderDTO;
use App\Entity\Order;
use App\Enum\StripeEventsType;
use App\Model\OrderModel;
use App\Model\PaymentModel;
use App\Repository\OrderRepository;
use Stripe\Charge;
use Stripe\Event as StripeEvent;
use Stripe\PaymentIntent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class PaymentIntentStripeListener
{
    public function __construct(
        private readonly PaymentModel    $paymentModel,
        private readonly OrderModel      $orderModel,
        private readonly OrderRepository $orderRepository,
    ) {}

    #[AsEventListener(event: StripeEvent::class)]
    public function onAmountCapturableUpdated(StripeEvent $event): void
    {

        if ($event->type === StripeEventsType::PaymentAmountCapturableUpdated->getLabel()) {

            /** @var PaymentIntent $paymentIntent */
            $paymentIntent = $event->data->object;

            $dateTime = new \DateTimeImmutable();
            $dateTime->setTimestamp($paymentIntent->created);

            $this->paymentModel->update(
                $paymentIntent->id,
                StripeEventsType::PaymentAmountCapturableUpdated->value,
                $dateTime,
            );
        }
    }

    #[AsEventListener(event: StripeEvent::class)]
    public function onCanceled(StripeEvent $event): void
    {
        if ($event->type === StripeEventsType::PaymentCanceled->getLabel()) {

            /** @var PaymentIntent $paymentIntent */
            $paymentIntent = $event->data->object;

            $dateTime = new \DateTimeImmutable();
            $dateTime->setTimestamp($paymentIntent->created);

            $this->paymentModel->update(
                $paymentIntent->id,
                StripeEventsType::PaymentCanceled->value,
                $dateTime,
            );
        }
    }

    #[AsEventListener(event: StripeEvent::class)]
    public function onCreated(StripeEvent $event): void
    {
        if ($event->type === StripeEventsType::PaymentCreated->getLabel()) {

            /** @var PaymentIntent $paymentIntent */
            $paymentIntent = $event->data->object;

            $order = $this->orderRepository->find($paymentIntent->metadata->order_id);

            $dateTime = new \DateTimeImmutable();
            $dateTime->setTimestamp($paymentIntent->created);

            $this->paymentModel->create(
                $paymentIntent->id,
                StripeEventsType::PaymentCreated->value,
                $dateTime,
                $order
            );
        }
    }

    #[AsEventListener(event: StripeEvent::class)]
    public function onPartiallyFunded(StripeEvent $event): void
    {
        if ($event->type === StripeEventsType::PaymentPartiallyFunded->getLabel()) {

            /** @var PaymentIntent $paymentIntent */
            $paymentIntent = $event->data->object;

            $dateTime = new \DateTimeImmutable();
            $dateTime->setTimestamp($paymentIntent->created);

            $this->paymentModel->update(
                $paymentIntent->id,
                StripeEventsType::PaymentPartiallyFunded->value,
                $dateTime,
            );
        }
    }

    #[AsEventListener(event: StripeEvent::class)]
    public function onPaymentFailed(StripeEvent $event): void
    {
        if ($event->type === StripeEventsType::PaymentPaymentFailed->getLabel()) {

            /** @var PaymentIntent $paymentIntent */
            $paymentIntent = $event->data->object;

            $dateTime = new \DateTimeImmutable();
            $dateTime->setTimestamp($paymentIntent->created);

            $this->paymentModel->update(
                $paymentIntent->id,
                StripeEventsType::PaymentPaymentFailed->value,
                $dateTime,
            );
        }
    }

    #[AsEventListener(event: StripeEvent::class)]
    public function onProcessing(StripeEvent $event): void
    {
        if ($event->type === StripeEventsType::PaymentProcessing->getLabel()) {

            /** @var PaymentIntent $paymentIntent */
            $paymentIntent = $event->data->object;

            $dateTime = new \DateTimeImmutable();
            $dateTime->setTimestamp($paymentIntent->created);

            $this->paymentModel->update(
                $paymentIntent->id,
                StripeEventsType::PaymentProcessing->value,
                $dateTime,
            );
        }
    }

    #[AsEventListener(event: StripeEvent::class)]
    public function onRequiresAction(StripeEvent $event): void
    {
        if ($event->type === StripeEventsType::PaymentRequiresAction->getLabel()) {

            /** @var PaymentIntent $paymentIntent */
            $paymentIntent = $event->data->object;

            $dateTime = new \DateTimeImmutable();
            $dateTime->setTimestamp($paymentIntent->created);

            $this->paymentModel->update(
                $paymentIntent->id,
                StripeEventsType::PaymentRequiresAction->value,
                $dateTime,
            );
        }
    }

    #[AsEventListener(event: StripeEvent::class)]
    public function onSucceeded(StripeEvent $event): void
    {
        if ($event->type === StripeEventsType::PaymentSucceeded->getLabel()) {

            /** @var PaymentIntent $paymentIntent */
            $paymentIntent = $event->data->object;

            $dateTime = new \DateTimeImmutable();
            $dateTime->setTimestamp($paymentIntent->created);

            $this->paymentModel->update(
                $paymentIntent->id,
                StripeEventsType::PaymentSucceeded->value,
                $dateTime,
            );
            $order = $this->orderRepository->find($paymentIntent->metadata->order_id);
            $this->orderModel->update($order, (new OrderDTO())->setStatus(Order::PAID));
        }
    }

    #[AsEventListener(event: StripeEvent::class)]
    public function onRefunded(StripeEvent $event): void
    {
        if ($event->type === StripeEventsType::ChargeRefunded->getLabel()) {

            /** @var Charge $charge */
            $charge = $event->data->object;

            $dateTime = new \DateTimeImmutable();
            $dateTime->setTimestamp($charge->created);

            $payment = $this->paymentModel->update(
                $charge->payment_intent,
                StripeEventsType::ChargeRefunded->value,
                $dateTime,
            );

            $this->orderModel->update($payment->getUserOrder(), (new OrderDTO())->setStatus(Order::CANCELLED));
        }
    }
}