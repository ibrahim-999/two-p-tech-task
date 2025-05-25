<?php

namespace App\Domains\Cart\ValueObjects;

class CartItem
{
    public function __construct(
        public readonly int $productId,
        public readonly int $quantity,
        public readonly float $price,
        public readonly string $productName
    ) {}

    public function getTotalPrice(): float
    {
        return $this->price * $this->quantity;
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'product_name' => $this->productName,
            'total_price' => $this->getTotalPrice(),
        ];
    }
}
