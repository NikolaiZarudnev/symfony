<?php

namespace App\Enum;

enum StripeEventsType: int
{
    case PaymentAmountCapturableUpdated = 1;
    case PaymentCanceled = 2;
    case PaymentCreated = 3;
    case PaymentPartiallyFunded = 4;
    case PaymentPaymentFailed = 5;
    case PaymentProcessing = 6;
    case PaymentRequiresAction = 7;
    case PaymentSucceeded = 8;
    case ChargeRefunded = 9;

    public function getLabel(): string
    {
        return match ($this) {
            StripeEventsType::PaymentAmountCapturableUpdated => 'payment_intent.amount_capturable_updated',
            StripeEventsType::PaymentCanceled => 'payment_intent.canceled',
            StripeEventsType::PaymentCreated => 'payment_intent.created',
            StripeEventsType::PaymentPartiallyFunded => 'payment_intent.partially_funded',
            StripeEventsType::PaymentPaymentFailed => 'payment_intent.payment_failed',
            StripeEventsType::PaymentProcessing => 'payment_intent.processing',
            StripeEventsType::PaymentRequiresAction => 'payment_intent.requires_action',
            StripeEventsType::PaymentSucceeded => 'payment_intent.succeeded',
            StripeEventsType::ChargeRefunded => 'charge.refunded',
        };
    }

    public static function getList(): array
    {
        return [
            'amount capturable updated' => self::PaymentAmountCapturableUpdated->value,
            'payment canceled' => self::PaymentCanceled->value,
            'payment created' => self::PaymentCreated->value,
            'partially funded' => self::PaymentPartiallyFunded->value,
            'payment failed' => self::PaymentPaymentFailed->value,
            'payment processing' => self::PaymentProcessing->value,
            'requires action' => self::PaymentRequiresAction->value,
            'payment succeeded' => self::PaymentSucceeded->value,
            'payment refunded' => self::ChargeRefunded->value,
        ];
    }
}