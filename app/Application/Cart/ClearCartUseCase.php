<?php

namespace App\Application\Cart;

use App\Domains\Cart\Services\CartService;

class ClearCartUseCase
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function execute($userId)
    {
        return $this->cartService->clearCart($userId);
    }
}
