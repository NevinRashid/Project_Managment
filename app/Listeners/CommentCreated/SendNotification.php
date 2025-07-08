<?php

namespace App\Listeners\CommentCreated;

use App\Events\CommentCreated;
use App\Jobs\SendCommentEmailJob;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendNotification
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
    public function handle(CommentCreated $event):void
    {
        $recipients =[];
        $data= [];
        // Identify those concerned according to the type to which the comment belongs.
        /* If the type is Task, the recipients are (the user assigned to the task,
            users working within the same project, the owner of the team to which the project belongs).
         */
        if($event->comment->commentable_type === 'App\Models\Task'){
            $model = Task::findOrfail($event->comment->commentable_id);
            $recipients =collect([
                $model->assigned_user_id,
                $model->project->team->owner_id ??null,
            ])-> merge($model->project->workers->pluck('id'))->filter()->unique();
            $data =[
                'task_id'   => $model->id,
                'name'      => $model->name,
                'deadline'  => $model->due_date,
                'project'   => $model->project->name,
            ];
        }

        /* If the type is Project, the recipients are (the user who created the project,
            Users working within the same project, owner of the team to which the project belongs).
         */
        elseif($event->comment->commentable_type === 'App\Models\Project'){
            $model = Project::findOrfail($event->comment->commentable_id);
            $recipients =collect([
                $model->created_by_user_id,
                $model->team->owner_id ??null,
            ])-> merge($model->workers->pluck('id'))->filter()->unique();
            $data =[
                'project_id'  => $model->id,
                'name'        => $model->name,
                'deadline'    => $model->due_date,
                'team'        => $model->team->name,
            ];
        }
        foreach($recipients as $recipient){
            $notification =[];
            $notification['user_id'] = $recipient ;
            $notification['type'] = 'comment_created';
            $notification['data'] =$data;
            $notification['read_at'] = null ;
            $notificationResult= $this->notificationService->createNotification($notification);
            SendCommentEmailJob::dispatch($notificationResult);
        }
    }
}
