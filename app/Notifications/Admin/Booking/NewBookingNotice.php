<?php

namespace App\Notifications\Admin\Booking;

use App\Channels\LineChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Booking;
use App\Http\Controllers\LineIntegrationController;

class BookingNotice extends Notification
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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $bookedFrom = \Carbon\Carbon::parse($this->booking->booked_from);
        $bookedTo = \Carbon\Carbon::parse($this->booking->booked_to);

        return (new MailMessage)
            ->subject('การจองใหม่รออนุมัติ - ' . $this->booking->booking_name . ' ' . $bookedFrom->format('Y-m-d') . ' - ' . (\App\Models\AppSetting::getSetting('name') ?? config('app.name', 'PSC-MusicWeb Project')))
            ->greeting('เรียน คุณ' . $notifiable->name . ',')
            ->line('คุณได้รับคำขออนุมัติการจองห้องดนตรีใหม่จากผู้ใช้ระบบ โดยมีรายละเอียดดังนี้:')
            ->line('')
            ->line('- ชื่อการจอง: ' . $this->booking->booking_name)
            ->line('- วันที่จอง: ' . $bookedFrom->locale('th')->isoFormat('DD MMMM YYYY'))
            ->line('- เวลา: ' . $bookedFrom->format('H:i') . ' - ' . $bookedTo->format('H:i'))
            ->line('- ผู้เข้าร่วม: ' . (!empty($this->booking->parseAttendeeforDisplay()) ? implode(', ', $this->booking->parseAttendeeforDisplay()) : 'ไม่ระบุผู้เข้าร่วม'))
            ->line('')
            ->line('คุณสามารถตรวจสอบและอนุมัติการจองได้ที่ลิงก์ด้านล่างนี้')
            ->action('ตรวจสอบและอนุมัติการจอง', route('admin.booking.detail', ['id' => $this->booking->booking_id]))
            ->line('ขอบคุณครับ/ค่ะ');
    }

    /**
     * Get the notification's delivery channels.
     * Only broadcast (toast) for admins, no LINE or mail
     */
    public function via(object $notifiable): array
    {
        return ['broadcast', 'mail', LineChannel::class];
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

    public function toLineIntegration(object $notifiable): string
    {
        // Import LINE Flex Template
        if ($notifiable->line_id == null) {
            return "No Line User ID";
        }

        $jStr = file_get_contents(app_path('line/flex_messages/booking/admin/new_booking_alert.json'));
        $fTem = json_decode($jStr, true);

        // Replace Placeholder daya to real data

        // Room Name
        $fTem['body']['contents'][2]['contents']['contents'][1]['text'] = $this->booking->room->room_name ?? 'ไม่ระบุ';

        // Date
        $fTem['body']['contents'][2]['contents'][1]['contents'][1]['text'] =
            \Carbon\Carbon::parse($this->booking->booked_from)->locale('th')->isoFormat('DD MMMM YYYY');

        // Time
        $fTem['body']['contents'][2]['contents'][2]['contents'][1]['text'] =
            \Carbon\Carbon::parse($this->booking->booked_from)->format('H:i') . ' - ' .
            \Carbon\Carbon::parse($this->booking->booked_to)->format('H:i');

        // Participants
        $attendeeList = $this->booking->parseAttendeeforDisplay();
        $fTem['body']['contents'][2]['contents'][3]['contents'][1]['text'] =
            !empty($attendeeList) ? implode(', ', $attendeeList) : 'ไม่ระบุผู้เข้าร่วม';

        // Booking Approval Link
        $fTem['footer']['contents'][0]['action']['uri'] = route('admin.booking.approve', ['id' => $this->booking->booking_id]);

        $lineCon = new LineIntegrationController();
        return $lineCon->pushFlexMessage($notifiable->line_id, "แจ้งเตือนการจองห้องเสร็จสิ้น", $fTem);
    }
}

