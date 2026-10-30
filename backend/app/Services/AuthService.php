<?php

namespace App\Services;

use App\Constants\CUser;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Tạo Sanctum Personal Access Token cho User
     */
    public function createAuthToken(User $user, string $deviceName = 'auth_token'): array
    {
        // delete all user tokens
        // $user->tokens()->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user,
        ];
    }

    /**
     * Logout
     */
    public function logout(User $user): void
    {
        /** @var PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();

        $token?->delete();
    }
}
