<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Cart\Models\Cart;
use App\Domains\Cart\Models\CartItem;
use App\Domains\Cart\Repositories\CartRepositoryInterface;
use App\Domains\User\Models\User;
use Illuminate\Support\Facades\DB;

class CartRepository implements CartRepositoryInterface
{
    public function __construct(protected Cart $model)
    {
    }
    public function findByUserId($userId)
    {
        return $this->model->with(['items.product'])->where('user_id', $userId)->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function addItem($cartId, array $itemData)
    {
        return DB::transaction(function () use ($cartId, $itemData) {
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
                ->firstOrFail();

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

    public function getItemCount($cartId): int
    {
        return CartItem::where('cart_id', $cartId)->sum('quantity');
    }

    public function getTotalAmount($cartId): float
    {
        return CartItem::where('cart_id', $cartId)
            ->with('product')
            ->get()
            ->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });
    }
}
