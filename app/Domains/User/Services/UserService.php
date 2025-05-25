<?php

namespace App\Domains\User\Services;

use App\Domains\User\Repositories\UserRepositoryInterface;
use App\Traits\CommonServiceCrudTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

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
        $user = $this->repository->create($data);

        $this->cacheUserProfile($user);

        return $user;
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

        $this->cacheUserProfile($user);

        return $user;
    }


    public function updateProfile($userId, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->repository->update($userId, $data);

        $this->cacheUserProfile($user);

        return $user;
    }

    public function getUserProfile($userId)
    {
        return Cache::remember("user_profile.{$userId}", 1800, function () use ($userId) {
            $user = $this->repository->findWithRelations($userId, ['cart', 'orders']);

            if (!$user) {
                return null;
            }

            return [
                'user' => $user,
                'statistics' => [
                    'total_orders' => $user->orders->count(),
                    'total_spent' => $user->orders->where('status', 'paid')->sum('total_amount'),
                    'pending_orders' => $user->orders->where('status', 'pending')->count(),
                    'cart_items' => $user->cart ? $user->cart->getItemsCount() : 0,
                    'member_since' => $user->created_at->diffForHumans(),
                ]
            ];
        });
    }

    private function cacheUserProfile($user): void
    {
        Cache::put("user_profile.{$user->id}", [
            'user' => $user,
            'cached_at' => now()
        ], 1800);
    }

    public function clearUserProfileCache($userId): void
    {
        Cache::forget("user_profile.{$userId}");

        if (method_exists($this->repository, 'clearUserCache')) {
            $this->repository->clearUserCache($userId);
        }
    }

    public function getUsers(array $filters = [])
    {
        $cacheKey = 'users.filtered.' . md5(serialize($filters));

        return Cache::remember($cacheKey, 1800, function () use ($filters) {
            if (empty($filters)) {
                return $this->repository->all();
            }

            return $this->repository->all();
        });
    }
}
