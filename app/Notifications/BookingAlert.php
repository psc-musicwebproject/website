<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\LineIntegrationController;
use App\Channels\LineChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Models\User;

class BookingAlert extends Notification
{
    use Queueable;

    protected $booking;
    protected $bookedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, User $bookedBy)
    {
        $this->booking = $booking;
        $this->bookedBy = $bookedBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', LineChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    public function toLineIntegration(object $notifiable): string
    {

        // Importing Line Flex Message Template from JSON file
        if ($notifiable->line_id == null) {
            return "No Line User ID";
        }

        if ($this->booking->user->type != 'admin') {
            $jsonString = file_get_contents(app_path('line/flex_messages/booking_alert.json'));
            $flexMessage = json_decode($jsonString, true);
        } elseif ($this->booking->user->type == 'admin' || $this->bookedBy->type == 'admin') {
            $jsonString = file_get_contents(app_path('line/flex_messages/booking_alert_admin.json'));
            $flexMessage = json_decode($jsonString, true);
        }

        // Replace placeholders with actual booking data
        // Room name
        $flexMessage['body']['contents'][2]['contents'][0]['contents'][1]['text'] =
            $this->booking->room->room_name ?? 'ไม่ระบุ';

        // Date
        $flexMessage['body']['contents'][2]['contents'][1]['contents'][1]['text'] =
            \Carbon\Carbon::parse($this->booking->booked_from)->locale('th')->isoFormat('DD MMMM YYYY');

        // Time
        $flexMessage['body']['contents'][2]['contents'][2]['contents'][1]['text'] =
            \Carbon\Carbon::parse($this->booking->booked_from)->format('H:i') . ' - ' .
            \Carbon\Carbon::parse($this->booking->booked_to)->format('H:i');

        // Participants
        $attendeeList = Booking::parseAttendeeforDisplay($this->booking->attendees);
        $flexMessage['body']['contents'][2]['contents'][3]['contents'][1]['text'] =
            !empty($attendeeList) ? implode(', ', $attendeeList) : 'ไม่ระบุผู้เข้าร่วม';

        // Update action URL (optional - link to booking details)
        if ($this->booking->user->type == 'admin') {
            $bookingUrl = route('admin.booking.detail', ['id' => $this->booking->booking_id]);
        } else {
            $bookingUrl = route('dash.booking.history.detail', ['id' => $this->booking->booking_id]);
        }
        $flexMessage['footer']['contents'][0]['action']['uri'] = $bookingUrl;

        $lineController = new LineIntegrationController();
        return $lineController->pushFlexMessage($notifiable->line_id, "แจ้งเตือนการจองห้องเสร็จสิ้น", $flexMessage);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
