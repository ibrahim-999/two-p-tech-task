<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cart_id' => $this->id,
            'user_id' => $this->user_id,
            'items_count' => $this->getItemsCount(),
            'total_amount' => $this->getTotalAmount(),
            'is_empty' => $this->items->isEmpty(),
            'items' => CartItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
