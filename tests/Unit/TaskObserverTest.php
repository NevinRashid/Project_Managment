<?php

namespace Tests\Unit;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskObserverTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_task_observer(): void
    {
        $task = Task::factory()->create(['status'=>null]);
        $this->assertEquals('pending',$task->status);
    }
}
