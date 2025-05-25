<?php

namespace App\Application\Product;

use App\Domains\Product\Repositories\ProductRepositoryInterface;

class GetProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute($id)
    {
        return $this->productRepository->findOrFail($id);
    }
}
