<?php

namespace App\Model;

use App\Entity\Order;
use App\Entity\Payment;
use App\Repository\PaymentRepository;

class PaymentModel
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository,
    ) {}

    public function create(string $paymentIntentId, int $status, \DateTimeImmutable $dateTime, Order $order): void
    {
        $payment = new Payment();
        $payment->setPaymentIntentId($paymentIntentId);
        $payment->setStatus($status);
        $payment->setCreatedAt($dateTime);
        $payment->setUserOrder($order);

        $this->paymentRepository->save($payment, true);
    }

    public function update(string $paymentIntentId, int $status, \DateTimeImmutable $dateTime): Payment
    {
        $payment = $this->paymentRepository->findOneBy(['paymentIntentId' => $paymentIntentId]);
        $payment->setStatus($status);
        $payment->setCreatedAt($dateTime);

        $this->paymentRepository->save($payment, true);

        return $payment;
    }
}