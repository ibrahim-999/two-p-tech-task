<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Cart\Models\Cart;
use App\Domains\Cart\Models\CartItem;
use App\Domains\Cart\Repositories\CartRepositoryInterface;

class CartRepository implements CartRepositoryInterface
{
    public function findByUserId($userId)
    {
        return Cart::with(['items.product'])->where('user_id', $userId)->first();
    }

    public function create(array $data)
    {
        return Cart::create($data);
    }

    public function addItem($cartId, array $itemData)
    {
        return DB::transaction(function () use ($cartId, $itemData) {
            // Lock cart items to prevent concurrent modifications
            $existingItem = CartItem::where('cart_id', $cartId)
                ->where('product_id', $itemData['product_id'])
                ->lockForUpdate()
                ->first();

            if ($existingItem) {
                $existingItem->increment('quantity', $itemData['quantity']);
                return $existingItem->fresh();
            }

            return CartItem::create([
                'cart_id' => $cartId,
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity']
            ]);
        });
    }

    public function updateItem($cartId, $productId, $quantity)
    {
        return DB::transaction(function () use ($cartId, $productId, $quantity) {
            $item = CartItem::where('cart_id', $cartId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (!$item) {
                throw new \Exception('Cart item not found');
            }

            $item->update(['quantity' => $quantity]);
            return $item->fresh();
        });
    }

    public function removeItem($cartId, $productId)
    {
        return CartItem::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->delete();
    }

    public function clearCart($cartId)
    {
        return CartItem::where('cart_id', $cartId)->delete();
    }
}
