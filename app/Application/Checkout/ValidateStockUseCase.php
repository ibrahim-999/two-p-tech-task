<?php

namespace App\Application\Checkout;

use App\Domains\Cart\Services\CartService;
use App\Domains\Product\Repositories\ProductRepositoryInterface;

class ValidateStockUseCase
{
    public function __construct(
        private CartService $cartService,
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute($userId)
    {
        $cart = $this->cartService->getCartContents($userId);

        if ($cart->items->isEmpty()) {
            throw new \Exception('Cart is empty');
        }

        $stockIssues = [];

        foreach ($cart->items as $item) {
            $product = $this->productRepository->findOrFail($item->product_id);

            if (! $product->isInStock($item->quantity)) {
                $stockIssues[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'requested_quantity' => $item->quantity,
                    'available_quantity' => $product->stock_quantity,
                ];
            }
        }

        if (! empty($stockIssues)) {
            throw new \Exception('Stock validation failed: '.json_encode($stockIssues));
        }

        return true;
    }
}
