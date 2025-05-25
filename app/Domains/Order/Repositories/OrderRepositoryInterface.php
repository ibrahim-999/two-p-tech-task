<?php

namespace App\Domains\Order\Repositories;

interface OrderRepositoryInterface
{
    public function create(array $data);
    public function find($id);
    public function findByUser($userId);
    public function update($id, array $data);
}
