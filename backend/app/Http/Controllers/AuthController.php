<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AuthService;
use App\Http\Resources\UserResource;
use App\Http\Requests\Auth\LoginRequest;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    /**
     * API: POST /api/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $request->authenticate();

        $tokenData = $this->authService->createAuthToken($user);

        return $this->success([
            'access_token' => $tokenData['access_token'],
            'token_type'   => $tokenData['token_type'],
            'user'         => new UserResource($user),
        ], 'Login successful');
    }

    /**
     * API: POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * API: GET /api/me
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource($request->user()),
            'User profile retrieved successfully'
        );
    }
}
