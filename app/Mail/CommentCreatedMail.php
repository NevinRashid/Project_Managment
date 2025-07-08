<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommentCreatedMail extends Mailable
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

        return $this->subject('There is a new comment')
                    ->view('emails.email_form')
                    ->with([
                        'type'  => $this->notification->type,
                        'data'  => $this->notification->data,
                        ]);
    }

}
