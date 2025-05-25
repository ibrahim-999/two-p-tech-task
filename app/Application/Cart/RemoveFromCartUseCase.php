<?php

namespace App\Application\Cart;

use App\Domains\Cart\Services\CartService;

class RemoveFromCartUseCase
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function execute($userId, $productId)
    {
        return $this->cartService->removeItem($userId, $productId);
    }
}
