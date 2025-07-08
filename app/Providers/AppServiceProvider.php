<?php

namespace App\Providers;

use App\Events\CommentCreated;
use App\Events\TaskAssigned;
use App\Events\TaskStatusCompleted;
use App\Listeners\CommentCreated\SendNotification;
use App\Listeners\TaskAssigned\CreateNotification;
use App\Listeners\TaskAssigned\SendEmail;
use App\Listeners\TaskStatus\SendTaskStatusNotification;
use App\Models\Attachment;
use App\Models\Task;
use App\Observers\AttachmentObserver;
use App\Observers\TaskObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Task::observe(TaskObserver::class);
        Attachment::observe(AttachmentObserver::class);

        


    }
}
