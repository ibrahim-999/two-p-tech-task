<?php

namespace App\Application\Cart;

use App\Domains\Cart\Services\CartService;

class GetCartUseCase
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function execute($userId)
    {
        $cart = $this->cartService->getCartContents($userId);

        return [
            'cart_id' => $cart->id,
            'user_id' => $cart->user_id,
            'total_amount' => $cart->getTotalAmount(),
            'items_count' => $cart->getItemsCount(),
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_description' => $item->product->description,
                    'unit_price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'total_price' => $item->quantity * $item->product->price,
                    'stock_available' => $item->product->stock_quantity
                ];
            }),
            'created_at' => $cart->created_at,
            'updated_at' => $cart->updated_at
        ];
    }
}
