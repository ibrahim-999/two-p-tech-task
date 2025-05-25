<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cart_id' => $this->resource['cart_id'],
            'items_count' => $this->resource['items_count'],
            'total_amount' => $this->resource['total_amount'],
            'is_empty' => $this->resource['is_empty'],
        ];
    }
}
