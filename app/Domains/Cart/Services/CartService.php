<?php

namespace App\Domains\Cart\Services;

use App\Domains\Cart\Repositories\CartRepositoryInterface;
use App\Domains\Product\Repositories\ProductRepositoryInterface;
use App\Traits\CommonServiceCrudTrait;

class CartService
{
    use CommonServiceCrudTrait;

    public function __construct(
        private CartRepositoryInterface $repository,
        private ProductRepositoryInterface $productRepository
    ) {}

    public function getOrCreateCart($userId)
    {
        $cart = $this->repository->findByUserId($userId);

        if (!$cart) {
            $cart = $this->repository->create(['user_id' => $userId]);
        }

        return $cart;
    }

    public function addItem($userId, $productId, $quantity)
    {
        $product = $this->productRepository->findOrFail($productId);

        if (!$product->isInStock($quantity)) {
            throw new \Exception('Product is out of stock');
        }

        $cart = $this->getOrCreateCart($userId);

        return $this->repository->addItem($cart->id, [
            'product_id' => $productId,
            'quantity' => $quantity
        ]);
    }

    public function updateItem($userId, $productId, $quantity)
    {
        $cart = $this->getOrCreateCart($userId);
        $product = $this->productRepository->findOrFail($productId);

        if (!$product->isInStock($quantity)) {
            throw new \Exception('Product is out of stock');
        }

        return $this->repository->updateItem($cart->id, $productId, $quantity);
    }

    public function removeItem($userId, $productId)
    {
        $cart = $this->getOrCreateCart($userId);
        return $this->repository->removeItem($cart->id, $productId);
    }

    public function getCartContents($userId)
    {
        return $this->getOrCreateCart($userId);
    }

    public function clearCart($userId)
    {
        $cart = $this->getOrCreateCart($userId);
        return $this->repository->clearCart($cart->id);
    }
}
