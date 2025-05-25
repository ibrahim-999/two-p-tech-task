<?php

namespace App\Domains\Payment\ValueObjects;

class PaymentRequest
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $orderId,
        public readonly array $customerInfo
    ) {}

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'order_id' => $this->orderId,
            'customer_info' => $this->customerInfo
        ];
    }
}
