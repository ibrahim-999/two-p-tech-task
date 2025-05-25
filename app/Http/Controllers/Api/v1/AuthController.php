<?php

namespace App\Http\Controllers\Api\v1;

use App\Application\User\LoginUseCase;
use App\Application\User\LogoutUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginRequest;
use App\Http\Resources\User\AuthUserResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected LoginUseCase $loginUseCase,
        protected LogoutUseCase $logoutUseCase
    ) {}

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->loginUseCase->execute(
                $request->email,
                $request->password
            );

            $userWithRelations = $result['user']->load(['cart', 'orders']);

            return $this->successResponse([
                'user' => new AuthUserResource($userWithRelations),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ], 'Login successful');

        } catch (\Exception $e) {
            return $this->errorResponse('Login failed: '.$e->getMessage(), 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            $this->logoutUseCase->execute($user);

            return $this->successResponse(null, 'Logout successful');

        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed: '.$e->getMessage(), 500);
        }
    }

    public function logoutCurrentToken(Request $request)
    {
        try {
            $user = Auth::user();
            $this->logoutUseCase->executeCurrentToken($user);

            return $this->successResponse(null, 'Logout from current device successful');

        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed: '.$e->getMessage(), 500);
        }
    }

    public function me(Request $request)
    {
        $user = Auth::user();

        $includeRelations = [];
        if ($request->get('include_cart')) {
            $includeRelations[] = 'cart';
        }
        if ($request->get('include_orders')) {
            $includeRelations[] = 'orders';
        }

        if (! empty($includeRelations)) {
            $user->load($includeRelations);
        }

        return $this->successResponse(
            new AuthUserResource($user),
            'User profile retrieved'
        );
    }

    public function refresh(Request $request)
    {
        $user = Auth::user();

        if (method_exists(app('App\Domains\User\Repositories\UserRepositoryInterface'), 'clearUserCache')) {
            app('App\Domains\User\Repositories\UserRepositoryInterface')->clearUserCache($user->id);
        }

        $freshUser = $user->fresh(['cart', 'orders']);

        return $this->successResponse(
            new AuthUserResource($freshUser),
            'User data refreshed'
        );
    }
}
