<?php

namespace App\Notifications\Admin\Broadcast;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

class AdminBookingToast extends Notification
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     * Only broadcast (toast) for admins, no LINE or mail
     */
    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => 'มีการจองห้องดนตรีใหม่โดย ' . $this->booking->user->name . ' ' . $this->booking->user->surname,
            'type' => 'new_booking',
            'booking_id' => $this->booking->id,
        ]);
    }

    /**
     * Get the broadcast channels for the notification.
     */
    public function broadcastOn(): array
    {
        return [];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
        ];
    }
}

