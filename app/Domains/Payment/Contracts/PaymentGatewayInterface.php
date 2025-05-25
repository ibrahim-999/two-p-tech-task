<?php

namespace App\Domains\Payment\Contracts;

interface PaymentGatewayInterface
{
    public function createPayment(array $paymentData): array;
    public function verifyPayment(string $paymentReference): array;
    public function getPaymentStatus(string $paymentReference): string;
}
