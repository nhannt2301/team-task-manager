<?php

namespace Tests\Feature;

use App\Constants\CUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserAssigneesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_only_regular_users_as_assignees(): void
    {
        $admin = User::factory()->create(['role' => CUser::ROLE_ADMIN]);
        $assignee = User::factory()->create([
            'name' => 'Alice Assignee',
            'role' => CUser::ROLE_USER,
        ]);
        User::factory()->create(['role' => CUser::ROLE_ADMIN]);
        Sanctum::actingAs($admin);

        $this->getJson('/api/users/assignees')
            ->assertOk()
            ->assertJsonPath('data.0.id', $assignee->id)
            ->assertJsonPath('data.0.name', 'Alice Assignee')
            ->assertJsonMissingPath('data.0.password')
            ->assertJsonCount(1, 'data');
    }

    public function test_regular_users_cannot_list_assignees(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => CUser::ROLE_USER]));

        $this->getJson('/api/users/assignees')->assertForbidden();
    }

    public function test_assignees_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/users/assignees')->assertUnauthorized();
    }
}
