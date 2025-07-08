<?php

namespace App\Services;

use App\Models\Notification;
use App\Traits\HandleServiceErrors;
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    use HandleServiceErrors;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get all notifications from database
     *
     * @return array $arraydata
     */
    public function getAllNotifications()
    {
        try{
            $notifications = Cache::remember('all_notifications', 3600, function(){
                    return  Notification::all();
                });
            return $notifications;

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Get a single notification with its relationships.
     *
     * @param  Notification $notification
     *
     * @return Notification $notification
     */
    public function showNotification(Notification $notification)
    {
        try{
            return $notification->load('user');

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Add new notification to the database.
     *
     * @param array $arraydata
     *
     * @return Notification $notification
     */
    public function createNotification(array $data)
    {
        try{
            $notifications = Notification::create($data);
            return $notifications;

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Update the specified notification in the database.
     *
     * @param array $arraydata
     * @param Notification $notification
     *
     * @return Notification $notification
     */

    public function updateNotificationt(array $data, Notification $notification){
        try{
            $notification->update(array_filter($data));
            return $notification;

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Delete the specified notification from the database.
     *
     * @param Notification $notification
     *
     * @return Notification $notification
     */

    public function deleteNotification(Notification $notification){
        try{
            return $notification->delete();

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

    /**
     * Add new notification to the database.
     *
     * @param Notification $notification
     *
     * @return Notification $notification
     */
    public function markNotificationAsRead(Notification $notification)
    {
        try{
            $notification = $notification->update([
                    'read_at' => now()
            ]);
            return $notification;

        } catch(\Throwable $th){
            return $this->error("An error occurred",500, $th->getMessage());
        }
    }

}
