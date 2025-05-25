<?php
namespace App\Domains\User\Repositories;

interface UserRepositoryInterface
{
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function find($id);
    public function findOrFail($id);
    public function all();
    public function paginate($perPage = 15);
    public function findByEmail(string $email);
}
