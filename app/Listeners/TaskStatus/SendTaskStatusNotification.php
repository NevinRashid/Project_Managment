<?php

namespace App\Listeners\TaskStatus;

use App\Events\TaskStatusCompleted;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTaskStatusNotification
{
    public $task;
    /**
     * Create the event listener.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Handle the event.
     */
    public function handle(TaskStatusCompleted $event): void
    {

    }
}
