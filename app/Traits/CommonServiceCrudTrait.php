<?php

namespace App\Traits;

trait CommonServiceCrudTrait
{
    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findOrFail($id)
    {
        return $this->repository->findOrFail($id);
    }

    public function all()
    {
        return $this->repository->all();
    }

    public function paginate($perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }
}
