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
        $existingItem = CartItem::where('cart_id', $cartId)
            ->where('product_id', $itemData['product_id'])
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $itemData['quantity']);
            return $existingItem;
        }

        return CartItem::create([
            'cart_id' => $cartId,
            'product_id' => $itemData['product_id'],
            'quantity' => $itemData['quantity']
        ]);
    }

    public function updateItem($cartId, $productId, $quantity)
    {
        return CartItem::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->update(['quantity' => $quantity]);
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
