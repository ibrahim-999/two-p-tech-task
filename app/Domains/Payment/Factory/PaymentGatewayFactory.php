<?php

namespace App\Domains\Payment\Factory;

use App\Domains\Payment\Contracts\PaymentGatewayInterface;
use App\Domains\Payment\Gateways\ClickPayGateway;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    public static function create(string $gateway = null): PaymentGatewayInterface
    {
        $gateway = $gateway ?? config('payment.default_gateway');

        return match (strtolower($gateway)) {
            'clickpay' => new ClickPayGateway(),
            default => throw new InvalidArgumentException("Unsupported payment gateway: {$gateway}")
        };
    }

    public static function getAvailableGateways(): array
    {
        return ['clickpay'];
    }
}

