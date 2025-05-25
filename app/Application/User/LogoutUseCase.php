<?php
namespace App\Application\User;

use App\Domains\User\Services\UserService;

class LogoutUseCase
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function execute($user)
    {
        $user->tokens()->delete();

        return true;
    }

    public function executeCurrentToken($user)
    {
        $user->currentAccessToken()->delete();

        return true;
    }
}
