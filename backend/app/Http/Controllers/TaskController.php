<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService) {}

    public function index(ListTasksRequest $request): JsonResponse
    {
        return $this->paginate(
            $this->taskService->listFor($request->user(), $request->validated()),
            'Tasks retrieved successfully',
            TaskResource::class,
        );
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createFor($request->user(), $request->validated());

        return $this->created(new TaskResource($task));
    }

    public function show(Request $request, int $task): JsonResponse
    {
        return $this->success(
            new TaskResource($this->taskService->findFor($request->user(), $task)),
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

        return $this->success(new TaskResource($updatedTask), 'Task updated successfully');
    }

    public function destroy(Request $request, int $task): Response
    {
        $this->taskService->deleteFor($request->user(), $task);

        return response()->noContent();
    }
}
