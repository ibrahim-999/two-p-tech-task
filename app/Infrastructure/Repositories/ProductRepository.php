<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Product\Models\Product;
use App\Domains\Product\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(protected Product $model)
    {
    }
    public function find($id)
    {
        return Cache::remember("product.{$id}", 3600, function () use ($id) {
            return $this->model->find($id);
        });
    }

    public function findOrFail($id)
    {
        return Cache::remember("product.{$id}", 3600, function () use ($id) {
            return $this->model->findOrFail($id);
        });
    }

    public function all()
    {
        return Cache::remember('products.all', 1800, function () {
            return $this->model->where('is_active', true)->get();
        });
    }

    public function paginate($perPage = 15)
    {
        return $this->model->where('is_active', true)->paginate($perPage);
    }

    public function create(array $data)
    {
        $product = $this->model->create($data);
        Cache::forget('products.all');
        return $product;
    }

    public function update($id, array $data)
    {
        $product = $this->model->findOrFail($id);
        $product->update($data);
        Cache::forget("product.{$id}");
        Cache::forget('products.all');
        return $product;
    }

    public function delete($id)
    {
        $product = $this->model->findOrFail($id);
        $result = $product->delete();
        Cache::forget("product.{$id}");
        Cache::forget('products.all');
        return $result;
    }

    public function findActiveProducts()
    {
        return Cache::remember('products.active', 1800, function () {
            return $this->model->where('is_active', true)
                ->where('stock_quantity', '>', 0)
                ->get();
        });
    }
}
