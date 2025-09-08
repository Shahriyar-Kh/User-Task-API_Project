<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TaskTest extends TestCase
{
    use DatabaseTransactions;

    public function test_task_belongs_to_user()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($user->id, $task->user->id);
    }
}
