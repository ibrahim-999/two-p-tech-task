<?php

namespace App\Application\Checkout;

use App\Domains\Cart\Services\CartService;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\OrderService;
use App\Domains\Payment\Services\PaymentService;
use Illuminate\Support\Facades\DB;

class InitiateCheckoutUseCase
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
        private PaymentService $paymentService,
        private ValidateStockUseCase $validateStockUseCase
    ) {}

    public function execute($userId, array $customerInfo = [])
    {
        return DB::transaction(function () use ($userId, $customerInfo) {
            $this->validateStockUseCase->execute($userId);

            $cart = $this->cartService->getCartContents($userId);
            $orderData = $this->prepareOrderData($userId, $cart);
            $order = $this->orderService->createOrder($orderData);

            $this->orderService->createOrderItems($order, $cart->items);

            $paymentResult = $this->paymentService->createPayment($order, $customerInfo);

            if (!$paymentResult['success']) {
                throw new \Exception('Failed to create payment: ' . $paymentResult['error']);
            }

            $this->orderService->updatePaymentReference($order->id, $paymentResult['transaction_reference']);

            return [
                'order' => $order->fresh(['items']),
                'payment_url' => $paymentResult['payment_url'],
                'transaction_reference' => $paymentResult['transaction_reference']
            ];
        });
    }

    private function prepareOrderData($userId, $cart): array
    {
        return [
            'user_id' => $userId,
            'order_number' => $this->generateOrderNumber(),
            'total_amount' => $cart->getTotalAmount(),
            'status' => Order::STATUS_PENDING,
            'payment_gateway' => 'clickpay'
        ];
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
    }
}
