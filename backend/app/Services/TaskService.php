<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;

class TaskService
{
    /**
     * @param  array{status?: string, search?: ?string, cursor?: ?string}  $filters
     */
    public function listFor(User $user, array $filters = []): CursorPaginator
    {
        return $this->visibleTasks($user)
            ->when(
                isset($filters['status']),
                fn(Builder $query) => $query->where('status', $filters['status']),
            )
            ->when(
                filled($filters['search'] ?? null),
                fn(Builder $query) => $query->where('title', 'like', '%' . $filters['search'] . '%'),
            )
            ->with('assignee:id,name,email')
            ->latest('id')
            ->cursorPaginate(15, ['*'], 'cursor', $filters['cursor'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createFor(User $user, array $attributes): Task
    {
        $attributes['assigned_to'] ??= $user->id;

        return Task::create($attributes)->load('assignee:id,name,email');
    }

    public function findFor(User $user, int $taskId): Task
    {
        return $this->visibleTasks($user)
            ->with('assignee:id,name,email')
            ->findOrFail($taskId);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateFor(User $user, int $taskId, array $attributes): Task
    {
        $task = $this->visibleTasks($user)->findOrFail($taskId);
        $task->update($attributes);

        return $task->load('assignee:id,name,email');
    }

    public function deleteFor(User $user, int $taskId): void
    {
        $this->visibleTasks($user)->findOrFail($taskId)->delete();
    }

    private function visibleTasks(User $user): Builder
    {
        return Task::query()
            ->when(! $user->isAdmin(), fn(Builder $query) => $query->where('assigned_to', $user->id));
    }
}
