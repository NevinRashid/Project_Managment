<?php

namespace App\Listeners\TaskAssigned;

use App\Events\TaskAssigned;
use App\Models\Notification;
use App\Models\Task;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateNotification
{
    protected $notificationService;
    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(TaskAssigned $event): void
    {
        $data =[
            'task_id'   => $event->task->id,
            'name'      => $event->task->name,
            'deadline'  => $event->task->due_date,
            'project'   => $event->task->project->name,
        ];

        $notification =[];
        $notification['user_id'] = $event->task->assigned_user_id ;
        $notification['type'] = 'task_assigned';
        $notification['data'] ='$data';
        $notification['read_at'] = now() ;

        $notificationResult = $this->notificationService->createNotification($notification);
        $event->notification =$notificationResult;
    }
}
