<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class NotificationController extends Controller
{
    use AuthorizesRequests;
    /**
     * This property is used to handle various operations related to notifications,
     *
     * @var NotificationService
     */
    protected $notificationService;

        /**
     * Summary of middleware
     * @return array<Middleware|string>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin', only:['index']),
        ];
    }

    /**
     * Constructor for the AttachmentController class.
     *
     * Initializes the $notificationService property via dependency injection.
     *
     * @param NotificationService $NotificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * This method return all notifications from database.
     */
    public function index()
    {
        $this->authorize('viewAny',Notification::class);
        return $this->success($this->notificationService->getAllNotifications());
    }

    /**
     * Get notification from database.
     * using the notificationService via the showNotification method
     *
     * @param Notification $notification
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Notification $notification)
    {
        $this->authorize('view',$notification);
        return $this->success($this->notificationService->showNotification($notification));
    }


    /**
     * Remove the specified notification from database.
     *
     * @param Notification $notification
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notification $notification)
    {
        $this->authorize('delete',$notification);
        $this->notificationService->deleteNotification($notification);
        return $this->success(null ,'Deleted successfuly');
    }

    /**
     * Mark the notification as read.
     *
     * @param Notification $notification
     *
     * @return \Illuminate\Http\Response
     */
    public function markAsRead(Notification $notification)
    {
        $this->authorize('mark',$notification);
        return $this->success($this->notificationService->markNotificationAsRead($notification),
                                'Marked as read successfuly');
    }
}
