<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     */
    public $collects = ProductResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total_products' => $this->collection->count(),
                'in_stock_count' => $this->collection
                    ->where('stock_quantity', '>', 0)
                    ->count(),
                'cached_at' => now()->toISOString(),
            ],
        ];
    }

    public function with(Request $request): array
    {
        return [
            'message' => 'Products retrieved successfully',
            'success' => true,
        ];
    }
}
