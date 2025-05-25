<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Order\Models\Order;
use App\Domains\Order\Repositories\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(array $data)
    {
        return Order::create($data);
    }

    public function find($id)
    {
        return Order::with(['items.product', 'user'])->find($id);
    }

    public function findByUser($userId)
    {
        return Order::with(['items'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByPaymentReference($paymentReference)
    {
        return Order::with(['items', 'user'])
            ->where('payment_reference', $paymentReference)
            ->first();
    }

    public function update($id, array $data)
    {
        $order = Order::findOrFail($id);
        $order->update($data);
        return $order->fresh();
    }
}
