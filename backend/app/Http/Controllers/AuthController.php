<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * API: POST /api/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login($request->validated());

        return response()->json([
            'message' => 'success',
            'data' => $data
        ], 200);
    }

    /**
     * API: POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'success'
        ], 200);
    }

    /**
     * API: GET /api/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()
        ], 200);
    }
}
