<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Cart\Models\Cart;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Repositories\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(protected Order $model)
    {
    }
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function find($id)
    {
        return $this->model->with(['items.product', 'user'])->find($id);
    }

    public function findByUser($userId)
    {
        return $this->model->with(['items'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByPaymentReference($paymentReference)
    {
        return $this->model->with(['items', 'user'])
            ->where('payment_reference', $paymentReference)
            ->first();
    }

    public function update($id, array $data)
    {
        $order = $this->model->findOrFail($id);
        $order->update($data);
        return $order->fresh();
    }
}
