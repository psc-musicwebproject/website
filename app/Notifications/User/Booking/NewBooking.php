<?php

namespace App\Notifications\User\Booking;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\LineIntegrationController;
use App\Channels\LineChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Models\User;

class NewBooking extends Notification
{
    use Queueable;

    protected $booking;
    protected $bookedBy;
    protected $isAdminBooking;
    protected $appName;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, User $bookedBy)
    {
        $this->booking = $booking;
        $this->bookedBy = $bookedBy;
        $this->isAdminBooking = $bookedBy->type === 'admin';
        $this->appName = \App\Models\AppSetting::getSetting('name') ?? config('app.name', 'PSC-MusicWeb Project');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', LineChannel::class, 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $roomName = $this->booking->room->room_name;
        $bookedFrom = \Carbon\Carbon::parse($this->booking->booked_from);
        $bookedTo = \Carbon\Carbon::parse($this->booking->booked_to);

        // Build subject
        $subject = $this->isAdminBooking
            ? 'บันทึกการจอง - ' . $this->booking->booking_name . ' ' . $bookedFrom->format('Y-m-d') . ' - ' . $this->appName
            : 'การจองสำเร็จ - รอการอนุมัติ - ' . $this->appName;

        $mail = (new MailMessage())
            ->subject($subject)
            ->greeting('เรียน คุณ' . $this->booking->user->name . ',');

        // Opening line based on who booked
        $openingLine = $this->isAdminBooking
            ? 'คุณหรือผู้ดูแลระบบได้ทำการบันทึกการจองห้อง "' . $roomName . '" สำเร็จแล้ว โดยมีรายละเอียดดังนี้:'
            : 'คุณได้ทำการจองห้อง "' . $roomName . '" สำเร็จแล้ว โดยมีรายละเอียดดังนี้:';

        $mail->line($openingLine)
            ->line('')
            ->line('- ชื่อการจอง: ' . $this->booking->booking_name)
            ->line('- วันที่จอง: ' . $bookedFrom->locale('th')->isoFormat('DD MMMM YYYY'))
            ->line('- เวลา: ' . $bookedFrom->format('H:i') . ' - ' . $bookedTo->format('H:i'))
            ->line('- ผู้เข้าร่วม: ' . (!empty($this->booking->parseAttendeeforDisplay()) ? implode(', ', $this->booking->parseAttendeeforDisplay()) : 'ไม่ระบุผู้เข้าร่วม'));

        // Note if booked by different user
        if ($this->bookedBy->id !== $this->booking->user->id) {
            $mail->line('')
                ->line('หมายเหตุ: การจองนี้ถูกสร้างขึ้นโดย ' . $this->bookedBy->name . ' ' . $this->bookedBy->surname);
        }

        // Status message based on admin or user
        if ($this->isAdminBooking) {
            $mail->line('การจองนี้ได้รับการอนุมัติทันทีเนื่องจากถูกสร้างโดยผู้ดูแลระบบ หากคุณมีคำถามหรือต้องการเปลี่ยนแปลงใด ๆ กรุณาติดต่อผู้ดูแลระบบ.')
                ->line('');
        } else {
            $mail->line('')
                ->line('โปรดรอการอนุมัติจากผู้ดูแลระบบ ระบบจะแจ้งเตือนเมื่อการจองของคุณได้รับการอนุมัติ')
                ->line('');
        }

        // Action button
        $detailRoute = $this->isAdminBooking
            ? route('admin.booking.detail', ['id' => $this->booking->booking_id])
            : route('dash.booking.history.detail', ['id' => $this->booking->booking_id]);

        $mail->line('ถ้าคุณต้องการดูรายละเอียดการจองเพิ่มเติม กรุณาคลิกที่ปุ่มด้านล่างนี้')
            ->action('ดูรายละเอียดการจอง', $detailRoute)
            ->line('ขอบคุณครับ/ค่ะ');

        return $mail;
    }

    public function toLineIntegration(object $notifiable): string
    {

        // Importing Line Flex Message Template from JSON file
        if ($notifiable->line_id == null) {
            return "No Line User ID";
        }

        if ($this->booking->user->type != 'admin') {
            $jsonString = file_get_contents(app_path('line/flex_messages/booking/new_booking.json'));
            $flexMessage = json_decode($jsonString, true);
        } elseif ($this->booking->user->type == 'admin' || $this->bookedBy->type == 'admin') {
            $jsonString = file_get_contents(app_path('line/flex_messages/booking/admin/new_booking.json'));
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
        $attendeeList = $this->booking->parseAttendeeforDisplay();
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
        return $lineController->pushFlexMessage($notifiable->line_id, "การจองห้องเสร็จสิ้น", $flexMessage);
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

    public function toBroadcast(): BroadcastMessage | null
    {
        $broad_msg = $this->isAdminBooking
            ? 'การจองห้องของคุณได้ถูกบันทึกเรียบร้อยแล้วโดยผู้ดูแลระบบ'
            : 'การจองห้องของคุณได้ถูกบันทึกเรียบร้อยแล้ว โปรดรอการอนุมัติจากผู้ดูแลระบบ';
        $redirectURI = $this->isAdminBooking
            ? route('admin.booking.detail', ['id' => $this->booking->booking_id])
            : route('dash.booking.history.detail', ['id' => $this->booking->booking_id]);

        if (!$this->isAdminBooking) {
            return new BroadcastMessage([
                'title' => 'การจองห้องเสร็จสิ้น',
                'message' => $broad_msg,
                'action_url' => $redirectURI,
                'type' => 'info'
            ]);
        }

        return null;
    }
}
