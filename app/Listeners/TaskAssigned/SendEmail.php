<?php

namespace App\Listeners\TaskAssigned;

use App\Events\TaskAssigned;
use App\Jobs\SendEmailJob;
use App\Mail\TaskAssignedMail;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendEmail
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
    public function handle(TaskAssigned $event): void
    {
        $notification = $event->notification;
        if($notification){
            SendEmailJob::dispatch($notification);
        }

    }
}
