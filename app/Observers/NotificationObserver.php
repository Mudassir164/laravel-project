<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\User;

class NotificationObserver
{
    public function created(Notification $notification)
    {
        $ref = $notification->ref;
        $user = $ref::find($notification->receiver_id);
        if (!empty($user)) {
            Notification::sendPushNotification($notification);
        }
    }
}
