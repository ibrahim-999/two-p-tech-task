<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product->name,
            'product_description' => $this->product->description,
            'unit_price' => $this->product->price,
            'quantity' => $this->quantity,
            'total_price' => $this->quantity * $this->product->price,
            'stock_available' => $this->product->stock_quantity,
            'is_available' => $this->product->is_active && $this->product->stock_quantity > 0,
        ];
    }
}
