<?php

namespace App\Infrastructure\Repositories;

use App\Domains\User\Models\User;
use App\Domains\User\Repositories\UserRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    protected $cacheTtl = 3600;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        $user = $this->model->create($data);

        $this->cacheUser($user);

        $this->clearUsersListCache();

        return $user;
    }

    public function update($id, array $data)
    {
        $user = $this->findOrFail($id);
        $user->update($data);

        $this->cacheUser($user->fresh());

        $this->clearUsersListCache();
        Cache::forget("user.email.{$user->email}");

        return $user->fresh();
    }

    public function delete($id)
    {
        $user = $this->findOrFail($id);
        $email = $user->email;

        $result = $user->delete();

        Cache::forget("user.{$id}");
        Cache::forget("user.email.{$email}");
        $this->clearUsersListCache();

        return $result;
    }

    public function find($id)
    {
        return Cache::remember("user.{$id}", $this->cacheTtl, function () use ($id) {
            return $this->model->find($id);
        });
    }

    public function findOrFail($id)
    {
        $user = $this->find($id);

        if (!$user) {
            throw new ModelNotFoundException(
                "User with ID {$id} not found"
            );
        }

        return $user;
    }

    public function all()
    {
        return Cache::remember('users.all', $this->cacheTtl, function () {
            return $this->model->all();
        });
    }

    public function paginate($perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    public function findByEmail(string $email)
    {
        return Cache::remember("user.email.{$email}", $this->cacheTtl, function () use ($email) {
            return $this->model->where('email', $email)->first();
        });
    }

    /**
     * Cache a user instance
     */
    private function cacheUser(User $user): void
    {
        Cache::put("user.{$user->id}", $user, $this->cacheTtl);
        Cache::put("user.email.{$user->email}", $user, $this->cacheTtl);
    }

    /**
     * Clear users list cache
     */
    private function clearUsersListCache(): void
    {
        Cache::forget('users.all');
    }

    /**
     * Get user with relationships cached
     */
    public function findWithRelations($id, array $relations = [])
    {
        $cacheKey = "user.{$id}.with." . implode('.', $relations);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id, $relations) {
            return $this->model->with($relations)->find($id);
        });
    }

    /**
     * Clear all user-related cache
     */
    public function clearUserCache($userId = null): void
    {
        if ($userId) {
            $user = $this->model->find($userId);
            if ($user) {
                Cache::forget("user.{$userId}");
                Cache::forget("user.email.{$user->email}");
            }
        }

        $this->clearUsersListCache();
    }
}
