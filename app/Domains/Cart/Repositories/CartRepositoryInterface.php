<?php

namespace App\Domains\Cart\Repositories;

interface CartRepositoryInterface
{
    public function findByUserId($userId);
    public function create(array $data);
    public function addItem($cartId, array $itemData);
    public function updateItem($cartId, $productId, $quantity);
    public function removeItem($cartId, $productId);
    public function clearCart($cartId);
}
