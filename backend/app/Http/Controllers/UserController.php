<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function assignees(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        return $this->success(
            $this->userService->listAssignees(),
            'Assignees retrieved successfully',
        );
    }
}
