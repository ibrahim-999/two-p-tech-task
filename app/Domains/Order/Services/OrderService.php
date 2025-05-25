<?php

namespace App\Domains\Order\Services;

use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Traits\CommonServiceCrudTrait;

class OrderService
{
    use CommonServiceCrudTrait;

    public function __construct(
        protected OrderRepositoryInterface $repository
    ) {}

    public function createOrder(array $data)
    {
        return $this->repository->create($data);
    }

    public function createOrderItems($order, $cartItems): void
    {
        foreach ($cartItems as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
                'product_name' => $item->product->name
            ]);
        }
    }

    public function updatePaymentReference($orderId, $paymentReference)
    {
        return $this->repository->update($orderId, [
            'payment_reference' => $paymentReference
        ]);
    }

    public function updateStatus($orderId, $status)
    {
        return $this->repository->update($orderId, [
            'status' => $status
        ]);
    }

    public function findByPaymentReference($paymentReference)
    {
        return $this->repository->findByPaymentReference($paymentReference);
    }

    public function getUserOrders($userId)
    {
        return $this->repository->findByUser($userId);
    }
    public function processSuccessfulPayment($order): void
    {
        foreach ($order->items as $item) {
            if ($item->product && $item->product->stock_quantity >= $item->quantity) {
                $item->product->decrement('stock_quantity', $item->quantity);
            }
        }

        if ($order->user->cart) {
            $order->user->cart->items()->delete();
        }
    }

}
