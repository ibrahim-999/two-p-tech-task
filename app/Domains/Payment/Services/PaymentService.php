<?php

namespace App\Domains\Payment\Services;

use App\Domains\Payment\Factory\PaymentGatewayFactory;

class PaymentService
{
    public function __construct(
        private PaymentGatewayFactory $paymentGatewayFactory
    ) {}

    public function createPayment($order, array $customerInfo): array
    {
        $gateway = $this->paymentGatewayFactory::create('clickpay');
        $paymentData = $this->buildPaymentData($order, $customerInfo);

        return $gateway->createPayment($paymentData);
    }

    public function verifyPayment(string $paymentReference): array
    {
        $gateway = $this->paymentGatewayFactory::create('clickpay');

        return $gateway->verifyPayment($paymentReference);
    }

    public function getPaymentStatus(string $paymentReference): string
    {
        $gateway = $this->paymentGatewayFactory::create('clickpay');

        return $gateway->getPaymentStatus($paymentReference);
    }

    private function buildPaymentData($order, array $customerInfo): array
    {
        return [
            'amount' => $order->total_amount,
            'currency' => config('payment.currency', 'EGP'),
            'order_number' => $order->order_number,
            'description' => "Payment for Order #{$order->order_number}",
            'customer' => $this->buildCustomerData($customerInfo),
            'shipping' => $this->buildShippingData($customerInfo),
        ];
    }

    private function buildCustomerData(array $customerInfo): array
    {
        return [
            'name' => $customerInfo['name'],
            'email' => $customerInfo['email'],
            'phone' => $customerInfo['phone'] ?? '01000000000',
            'address' => $customerInfo['address'] ?? 'Cairo, Egypt',
            'city' => $customerInfo['city'] ?? 'cairo',
            'state' => $customerInfo['state'] ?? 'cairo',
            'country' => $customerInfo['country'] ?? 'EG',
            'zip' => $customerInfo['zip'] ?? '12345',
        ];
    }

    private function buildShippingData(array $customerInfo): array
    {
        return [
            'name' => $customerInfo['shipping_name'] ?? $customerInfo['name'],
            'email' => $customerInfo['shipping_email'] ?? $customerInfo['email'],
            'phone' => $customerInfo['shipping_phone'] ?? $customerInfo['phone'] ?? '01000000000',
            'address' => $customerInfo['shipping_address'] ?? $customerInfo['address'] ?? 'Cairo, Egypt',
            'city' => $customerInfo['shipping_city'] ?? $customerInfo['city'] ?? 'cairo',
            'state' => $customerInfo['shipping_state'] ?? $customerInfo['state'] ?? 'cairo',
            'country' => $customerInfo['shipping_country'] ?? $customerInfo['country'] ?? 'EG',
            'zip' => $customerInfo['shipping_zip'] ?? $customerInfo['zip'] ?? '12345',
        ];
    }
}
