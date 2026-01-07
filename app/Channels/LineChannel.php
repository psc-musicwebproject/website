<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Http\Controllers\LineIntegrationController;
use Illuminate\Support\Facades\Log;

class LineChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        Log::info('LineChannel: Attempting to send notification', [
            'notifiable_id' => $notifiable->id,
            'notifiable_class' => get_class($notifiable),
            'notification_class' => get_class($notification),
            'has_line_id' => !empty($notifiable->line_id)
        ]);

        // Check if the notifiable has a LINE user ID
        if (empty($notifiable->line_id)) {
            Log::warning('LineChannel: No LINE ID found for user', ['user_id' => $notifiable->id]);
            return;
        }

        // Get the LINE message from the notification
        $message = $notification->toLineIntegration($notifiable);
        
        Log::info('LineChannel: Notification sent', ['message' => $message]);
    }
}
