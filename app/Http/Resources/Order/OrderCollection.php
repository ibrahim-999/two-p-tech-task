<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderCollection extends ResourceCollection
{
    public $collects = OrderResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total_orders' => $this->collection->count(),
                'paid_orders' => $this->collection->where('status', 'paid')->count(),
            ],
        ];
    }

    public function with(Request $request): array
    {
        return [
            'message' => 'Orders retrieved successfully',
            'success' => true,
        ];
    }
}
