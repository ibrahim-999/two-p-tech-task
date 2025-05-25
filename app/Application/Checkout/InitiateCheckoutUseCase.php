<?php

namespace App\Application\Checkout;

use App\Domains\Cart\Services\CartService;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Domains\Payment\Factory\PaymentGatewayFactory;
use App\Domains\Product\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class InitiateCheckoutUseCase
{
    public function __construct(
        private CartService $cartService,
        private OrderRepositoryInterface $orderRepository,
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute($userId, array $customerInfo = [])
    {
        return DB::transaction(function () use ($userId, $customerInfo) {
            $cart = $this->cartService->getCartContents($userId);

            if ($cart->items->isEmpty()) {
                throw new \Exception('Cart is empty');
            }

            foreach ($cart->items as $item) {
                $product = $this->productRepository->findOrFail($item->product_id);

                if (!$product->isInStock($item->quantity)) {
                    throw new \Exception("Product '{$product->name}' is out of stock or insufficient quantity available");
                }
            }

            $orderNumber = $this->generateOrderNumber();
            $totalAmount = $cart->getTotalAmount();

            $order = $this->orderRepository->create([
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'status' => Order::STATUS_PENDING,
                'payment_gateway' => 'clickpay'
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    'product_name' => $item->product->name
                ]);
            }

            $paymentGateway = PaymentGatewayFactory::create('clickpay');

            $paymentData = [
                'amount' => $totalAmount,
                'currency' => config('payment.currency', 'SAR'),
                'order_number' => $orderNumber,
                'description' => "Payment for Order #{$orderNumber}",
                'customer' => [
                    'name' => $customerInfo['name'] ?? 'Customer',
                    'email' => $customerInfo['email'] ?? 'customer@example.com',
                    'phone' => $customerInfo['phone'] ?? '',
                    'address' => $customerInfo['address'] ?? '',
                    'city' => $customerInfo['city'] ?? 'Riyadh',
                    'country' => $customerInfo['country'] ?? 'SA'
                ]
            ];

            $paymentResult = $paymentGateway->createPayment($paymentData);

            if (!$paymentResult['success']) {
                throw new \Exception('Failed to create payment: ' . $paymentResult['error']);
            }

            // Step 6: Update order with payment reference
            $this->orderRepository->update($order->id, [
                'payment_reference' => $paymentResult['transaction_reference']
            ]);

            return [
                'order' => $order->fresh(['items']),
                'payment_url' => $paymentResult['payment_url'],
                'transaction_reference' => $paymentResult['transaction_reference']
            ];
        });
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
    }
}
