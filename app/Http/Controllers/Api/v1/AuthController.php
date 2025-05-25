<?php
namespace App\Http\Controllers\Api\v1;

use App\Application\User\LoginUseCase;
use App\Application\User\LogoutUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected LoginUseCase $loginUseCase, protected LogoutUseCase $logoutUseCase)
    {
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->loginUseCase->execute(
                $request->email,
                $request->password
            );

            return $this->successResponse([
                'user' => $result['user'],
                'token' => $result['token']
            ], 'Login successful');

        } catch (\Exception $e) {
            return $this->errorResponse('Login failed: ' . $e->getMessage(), 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            $this->logoutUseCase->execute($user);

            return $this->successResponse(null, 'Logout successful');

        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed: ' . $e->getMessage(), 500);
        }
    }

    public function logoutCurrentToken(Request $request)
    {
        try {
            $user = Auth::user();
            $this->logoutUseCase->executeCurrentToken($user);

            return $this->successResponse(null, 'Logout from current device successful');

        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed: ' . $e->getMessage(), 500);
        }
    }

    public function me(Request $request)
    {
        return $this->successResponse(Auth::user(), 'User profile retrieved');
    }
}
