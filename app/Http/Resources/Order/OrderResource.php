<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'payment_gateway' => $this->payment_gateway,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
