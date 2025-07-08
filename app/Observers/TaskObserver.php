<?php

namespace App\Observers;

use App\Events\TaskStatusCompleted;
use App\Models\Task;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function creating(Task $task): void
    {
        // When you create a task, it can set a default status is pending
        if (empty($task->status)) {
            $task->status = 'pending';
        }
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        //When a task's status is updated to Completed,it triggers TaskStatusCompleted event.
        event(new TaskStatusCompleted($task));
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        //
    }
}
