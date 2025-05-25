<?php

namespace App\Application\Cart;

use App\Domains\Cart\Services\CartService;

class UpdateCartItemUseCase
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function execute($userId, $productId, $quantity)
    {
        return $this->cartService->updateItem($userId, $productId, $quantity);
    }
}
