<?php

namespace Tests\Feature;

use App\Constants\CUser;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_crud_is_available_to_authenticated_users(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $created = $this->postJson('/api/tasks', [
            'title' => 'Prepare release',
            'description' => 'Review open items',
            'status' => 'todo',
            'due_date' => '2026-11-01',
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.title', 'Prepare release')
            ->assertJsonPath('data.assigned_to', $user->id);

        $taskId = $created->json('data.id');

        $this->getJson("/api/tasks/{$taskId}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Prepare release');

        $this->patchJson("/api/tasks/{$taskId}", ['status' => 'in_progress'])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        $this->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonPath('data.0.id', $taskId)
            ->assertJsonPath('meta.pagination.current_page', 1);

        $this->deleteJson("/api/tasks/{$taskId}")->assertNoContent();
        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }

    public function test_users_cannot_access_or_assign_tasks_owned_by_other_users(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherTask = Task::create([
            'title' => 'Private task',
            'assigned_to' => $otherUser->id,
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/tasks/{$otherTask->id}")->assertNotFound();

        $this->postJson('/api/tasks', [
            'title' => 'Invalid assignment',
            'assigned_to' => $otherUser->id,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('assigned_to');
    }

    public function test_admins_can_list_and_assign_tasks_to_other_users(): void
    {
        $admin = User::factory()->create(['role' => CUser::ROLE_ADMIN]);
        $assignee = User::factory()->create();
        Sanctum::actingAs($admin);

        $created = $this->postJson('/api/tasks', [
            'title' => 'Assigned task',
            'assigned_to' => $assignee->id,
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.assigned_to', $assignee->id);

        $this->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonPath('data.0.assigned_to', $assignee->id)
            ->assertJsonPath('meta.pagination.current_page', 1);
    }

    public function test_tasks_can_be_filtered_by_status_and_title_search(): void
    {
        $admin = User::factory()->create(['role' => CUser::ROLE_ADMIN]);
        Sanctum::actingAs($admin);

        $matchingTask = Task::create([
            'title' => 'Prepare in progress release',
            'status' => 'in_progress',
            'assigned_to' => User::factory()->create()->id,
        ]);
        Task::create([
            'title' => 'Prepare completed release',
            'status' => 'done',
            'assigned_to' => User::factory()->create()->id,
        ]);
        Task::create([
            'title' => 'Review in progress release',
            'status' => 'in_progress',
            'assigned_to' => User::factory()->create()->id,
        ]);

        $this->getJson('/api/tasks?status=in_progress&search=Prepare')
            ->assertOk()
            ->assertJsonPath('data.0.id', $matchingTask->id)
            ->assertJsonCount(1, 'data');
    }

    public function test_task_list_rejects_invalid_or_unapproved_filters(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => CUser::ROLE_ADMIN]));

        $this->getJson('/api/tasks?status=unknown')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->getJson('/api/tasks?assigned_to=1')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('query');

        $this->getJson('/api/tasks?search='.str_repeat('a', 101))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('search');
    }

    public function test_task_endpoints_require_authentication(): void
    {
        $this->getJson('/api/tasks')->assertUnauthorized();
    }
}
