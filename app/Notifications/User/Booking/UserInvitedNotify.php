<?php

namespace App\Notifications\User\Booking;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Models\User;
use App\Http\Controllers\LineIntegrationController;
use App\Channels\LineChannel;

class UserInvitedNotify extends Notification
{
    use Queueable;

    protected Booking $booking;
    protected ?string $guestName;

    /**
     * Create a new notification instance.
     *
     * @param Booking $booking The booking the user is invited to
     * @param string|null $guestName Optional name for guest (non-registered) users
     */
    public function __construct(Booking $booking, ?string $guestName = null)
    {
        $this->booking = $booking;
        $this->guestName = $guestName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // For guests (on-demand notifications), only send mail
        if ($notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable) {
            return ['mail'];
        }
        return ['mail', LineChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Determine the recipient's name
        $recipientName = $this->guestName;
        if (!$recipientName && $notifiable instanceof User) {
            $recipientName = $notifiable->name . ' ' . $notifiable->surname;
        }
        $recipientName = $recipientName ?: 'Guest';

        $roomName = $this->booking->room?->room_name ?? 'N/A';
        $bookedDate = $this->booking->booked_from?->format('d/m/Y') ?? 'N/A';
        $bookedTime = ($this->booking->booked_from?->format('H:i') ?? '') . ' - ' . ($this->booking->booked_to?->format('H:i') ?? '');

        return (new MailMessage)
            ->subject('คุณได้รับเชิญเข้าร่วมการจองห้อง - ' . $this->booking->booking_name . ' - ' . (\App\Models\AppSetting::getSetting('name') ?? config('app.name', 'PSC-MusicWeb Project')))
            ->greeting('เรียน คุณ' . $recipientName)
            ->line('คุณได้รับเชิญให้เข้าร่วมการจองห้องจาก ' . $this->booking->user->name_title . $this->booking->user->name . ' ' . $this->booking->user->surname . ' โดยมีรายละเอียดดังนี้:')
            ->line('ชื่อการจอง: ' . $this->booking->booking_name)
            ->line('ห้อง: ' . $roomName)
            ->line('วันที่: ' . $bookedDate)
            ->line('เวลา: ' . $bookedTime)
            ->line('')
            ->line('ขอให้คุณมีวันที่ดีครับ/ค่ะ');
    }

    public function toLineIntegration(object $notifiable): string {
        // Checking their LINE ID
        if ($notifiable->line_id === null) {
            return 'No Line User ID';
        }

        // Importing Line Flex Message Template from JSON file
        $jStr = file_get_contents(app_path('line/flex_messages/booking/user_invited.json'));
        $fTem = json_decode($jStr, true);

        // Replace placeholders with real data
        $fTem['body']['contents'][1]['contents'][0]['text'] = $this->booking->user->name_title . $this->booking->user->name . ' ' . $this->booking->user->surname . ' ได้เชิญคุณให้เข้าร่วมการใช้ห้อง "' . $this->booking->room->room_name . '" กรุณาตรวจสอบรายละเอียดการจองด้านล่าง';

        // Replace placeholders with actual booking data
        // Room name
        $fTem['body']['contents'][2]['contents'][0]['contents'][1]['text'] =
            $this->booking->room->room_name ?? 'ไม่ระบุ';

        // Date
        $fTem['body']['contents'][2]['contents'][1]['contents'][1]['text'] =
            $this->booking->booked_from->locale('th')->isoFormat('DD MMMM YYYY');

        // Time
        $fTem['body']['contents'][2]['contents'][2]['contents'][1]['text'] =
            $this->booking->booked_from->format('H:i') . ' - ' .
            $this->booking->booked_to->format('H:i');

        // Participants
        $attendeeList = $this->booking->parseAttendeeforDisplay();
        $fTem['body']['contents'][2]['contents'][3]['contents'][1]['text'] =
            !empty($attendeeList) ? implode(', ', $attendeeList) : 'ไม่ระบุผู้เข้าร่วม';

        $lineCon = new LineIntegrationController();
        return $lineCon->pushFlexMessage($notifiable->line_id, "ผลการจอง" . $this->booking->room->room_name, $fTem);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->booking_id,
            'booking_name' => $this->booking->booking_name
        ];
    }
}
