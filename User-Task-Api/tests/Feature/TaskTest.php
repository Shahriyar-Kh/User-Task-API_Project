<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TaskTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function user_can_create_task()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user); // Fixed: use 'api' guard

        $payload = ['title' => 'My Task', 'description' => 'Test description'];

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson('/api/tasks', $payload)
             ->assertCreated()
             ->assertJsonPath('title', 'My Task');
    }

    /** @test */
    public function user_can_list_their_tasks()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user); // Fixed: use 'api' guard

        Task::factory()->count(2)->create(['user_id' => $user->id]);

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson('/api/tasks')
             ->assertOk()
             ->assertJsonPath('data.0.title', function($value) { // Fixed: paginated response
                 return is_string($value);
             });
    }

    /** @test */
    public function user_can_view_single_task()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user); // Fixed: use 'api' guard

        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson("/api/tasks/{$task->id}")
             ->assertOk()
             ->assertJsonPath('id', $task->id);
    }

    /** @test */
    public function user_can_update_task()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user); // Fixed: use 'api' guard

        $task = Task::factory()->create(['user_id' => $user->id]);

        $payload = ['title' => 'Updated Task'];

        $this->withHeader('Authorization', "Bearer $token")
             ->putJson("/api/tasks/{$task->id}", $payload)
             ->assertOk()
             ->assertJsonPath('title', 'Updated Task');
    }

    /** @test */
    public function user_can_delete_task()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user); // Fixed: use 'api' guard

        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->withHeader('Authorization', "Bearer $token")
             ->deleteJson("/api/tasks/{$task->id}")
             ->assertOk() // Fixed: should be 200, not 204 based on controller
             ->assertJson(['message' => 'deleted']);
    }
}