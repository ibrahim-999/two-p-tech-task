<?php
// app/Application/Product/GetProductsUseCase.php

namespace App\Application\Product;

use App\Domains\Product\Repositories\ProductRepositoryInterface;

class GetProductsUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(array $filters = [])
    {
        $perPage = $filters['per_page'] ?? 15;

        if (isset($filters['active_only']) && $filters['active_only']) {
            // For active products only, use pagination but filter active products
            return $this->productRepository->paginate($perPage);
        }

        return $this->productRepository->paginate($perPage);
    }
}
