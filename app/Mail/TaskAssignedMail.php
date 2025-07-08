<?php

namespace App\Mail;

use App\Models\Notification;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $notification;
    /**
     * Create a new message instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function build()
    {
        return $this->subject('A task has been assigned to you')
                    ->view('emails.email_form')
                    ->with([
                        'type'  => $this->notification->type,
                        'data'  => $this->notification->data,
                        ]);
    }
}
