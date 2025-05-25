<?php

namespace App\Application\Order;

use App\Domains\Order\Repositories\OrderRepositoryInterface;

class GetUserOrdersUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function execute($userId)
    {
        return $this->orderRepository->findByUser($userId);
    }
}
