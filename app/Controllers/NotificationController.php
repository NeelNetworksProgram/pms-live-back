<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\NotificationModel;

class NotificationController extends BaseController
{

    // function for add new notification 
    public function addNewNotification($for,$notification_message,$to)
    {

        $notification = new NotificationModel();

        // insert new notification on database
        $insert_notification = [
            'notification_for'=>$for,
            'notification_message'=>$notification_message,
            'notification_to'=>$to
        ];

        $notification->insert($insert_notification);
    }



}
