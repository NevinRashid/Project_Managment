<?php

namespace App\Jobs;

use App\Mail\CommentCreatedMail;
use App\Mail\TaskAssignedMail;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendCommentEmailJob implements ShouldQueue
{
    use Queueable;

    protected $notification;
    /**
     * Create a new job instance.
     */
    public function __construct($notification)
    {
        $this->notification=$notification;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $email = $this->notification->user->email;
        Mail::to($email)->send(new CommentCreatedMail($this->notification));
    }
}
