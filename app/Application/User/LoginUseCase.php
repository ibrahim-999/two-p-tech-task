<?php
namespace App\Application\User;

use App\Domains\User\Services\UserService;

class LoginUseCase
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function execute(string $email, string $password)
    {
        $user = $this->userService->validateCredentials($email, $password);

        if (!$user) {
            throw new \Exception('Invalid credentials');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }
}
