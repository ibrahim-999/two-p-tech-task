<?php

namespace App\Domains\Product\Repositories;

interface ProductRepositoryInterface
{
    public function find($id);

    public function findOrFail($id);

    public function all();

    public function paginate($perPage = 15);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function findActiveProducts();
}
