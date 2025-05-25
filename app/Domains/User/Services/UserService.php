<?php

namespace App\Domains\User\Services;

use App\Domains\User\Repositories\UserRepositoryInterface;
use App\Traits\CommonServiceCrudTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class UserService
{
    use CommonServiceCrudTrait;

    public function __construct(protected UserRepositoryInterface $repository) {}

    public function findByEmail(string $email)
    {
        return $this->repository->findByEmail($email);
    }

    public function validateCredentials(string $email, string $password)
    {
        $user = $this->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            return false;
        }

        $this->cacheUserProfile($user);

        return $user;
    }

    private function cacheUserProfile($user): void
    {
        Cache::put("user_profile.{$user->id}", [
            'user' => $user,
            'cached_at' => now(),
        ], 1800);
    }
}
