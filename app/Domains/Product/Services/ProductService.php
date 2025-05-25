<?php

namespace App\Domains\Product\Services;

use App\Domains\Product\Repositories\ProductRepositoryInterface;
use App\Traits\CommonServiceCrudTrait;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    use CommonServiceCrudTrait;

    public function __construct(protected ProductRepositoryInterface $repository) {}

    public function getActiveProducts(array $filters = [])
    {
        $perPage = $filters['per_page'] ?? 15;

        if (isset($filters['paginate']) && $filters['paginate']) {
            return $this->repository->paginate($perPage);
        }

        return $this->repository->findActiveProducts();
    }

    public function getProductWithStock($id)
    {
        $product = $this->repository->findOrFail($id);

        return [
            'product' => $product,
            'in_stock' => $product->isInStock(),
            'stock_status' => $this->getStockStatus($product),
        ];
    }

    public function updateStock($productId, int $quantity)
    {
        $product = $this->repository->findOrFail($productId);

        $updatedProduct = $this->repository->update($productId, [
            'stock_quantity' => $quantity,
        ]);

        $this->clearProductCaches($productId);

        return $updatedProduct;
    }

    public function reduceStock($productId, int $quantity)
    {
        $product = $this->repository->findOrFail($productId);

        if (! $product->isInStock($quantity)) {
            throw new \Exception("Insufficient stock for product: {$product->name}");
        }

        $product->reduceStock($quantity);

        $this->clearProductCaches($productId);

        return $product->fresh();
    }

    private function getStockStatus($product): string
    {
        if ($product->stock_quantity === 0) {
            return 'out_of_stock';
        } elseif ($product->stock_quantity <= 5) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    private function clearProductCaches($productId): void
    {
        Cache::forget("product.{$productId}");
        Cache::forget('products.all');
        Cache::forget('products.active');

        $keys = Cache::getRedis()->keys('*products.search*');
        foreach ($keys as $key) {
            Cache::forget(str_replace(config('cache.prefix').':', '', $key));
        }

        $keys = Cache::getRedis()->keys('*products.price_range*');
        foreach ($keys as $key) {
            Cache::forget(str_replace(config('cache.prefix').':', '', $key));
        }
    }
}
