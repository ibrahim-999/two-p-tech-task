<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemActionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'item_id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'action_performed' => $this->additional['action'] ?? 'updated',
            'cart_summary' => [
                'total_items' => $this->additional['cart_items_count'] ?? 0,
                'total_amount' => $this->additional['cart_total'] ?? 0,
            ]
        ];
    }
}
