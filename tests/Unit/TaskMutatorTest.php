<?php

namespace Tests\Unit;

use App\Models\Task;
use PHPUnit\Framework\TestCase;

class TaskMutatorTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_description_mutator_strips_html_tags()
    {
        $task = new Task();
        $task->description = '<h1>Hello</h1><p> this is new task</p>';
        $this->assertEquals('Hello this is new task', $task->description);
    }
}
