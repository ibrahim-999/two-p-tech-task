<?php
namespace App\Domains\User\Services;

use App\Domains\User\Repositories\UserRepositoryInterface;
use App\Traits\CommonServiceCrudTrait;
use Illuminate\Support\Facades\Hash;

class UserService
{
    use CommonServiceCrudTrait;

    protected $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function createUser(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return $this->repository->create($data);
    }

    public function findByEmail(string $email)
    {
        return $this->repository->findByEmail($email);
    }

    public function validateCredentials(string $email, string $password)
    {
        $user = $this->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            return false;
        }

        return $user;
    }
}
