<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService) {}

    public function index(ListTasksRequest $request): JsonResponse
    {
        return $this->paginate(
            $this->taskService->listFor($request->user(), $request->validated()),
            'Tasks retrieved successfully',
        );
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createFor($request->user(), $request->validated());

        return $this->created($task);
    }

    public function show(Request $request, int $task): JsonResponse
    {
        return $this->success(
            $this->taskService->findFor($request->user(), $task),
            'Task retrieved successfully',
        );
    }

    public function update(UpdateTaskRequest $request, int $task): JsonResponse
    {
        $updatedTask = $this->taskService->updateFor(
            $request->user(),
            $task,
            $request->validated(),
        );

        return $this->success($updatedTask, 'Task updated successfully');
    }

    public function destroy(Request $request, int $task): JsonResponse
    {
        $this->taskService->deleteFor($request->user(), $task);

        return $this->success(null, 'Task deleted successfully', 204);
    }
}
