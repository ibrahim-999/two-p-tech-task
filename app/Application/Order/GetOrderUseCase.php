<?php

namespace App\Application\Order;

use App\Domains\Order\Repositories\OrderRepositoryInterface;

class GetOrderUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function execute($orderId)
    {
        return $this->orderRepository->find($orderId);
    }
}
