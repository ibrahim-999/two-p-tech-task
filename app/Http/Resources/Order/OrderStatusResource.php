<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order' => new OrderResource($this->resource),
            'payment_status' => $this->status,
            'success' => $this->status === 'paid',
        ];
    }
}
