<?php

namespace App\Application\Product;

namespace App\Application\Product;

use App\Domains\Product\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ReduceStockUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute($productId, $quantity)
    {
        return DB::transaction(function () use ($productId, $quantity) {
            $product = DB::table('products')
                ->where('id', $productId)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                throw new \Exception('Product not found');
            }

            if ($product->stock_quantity < $quantity) {
                throw new \Exception("Insufficient stock for product ID {$productId}");
            }

            DB::table('products')
                ->where('id', $productId)
                ->update(['stock_quantity' => $product->stock_quantity - $quantity]);

            return $this->productRepository->find($productId);
        });
    }
}

