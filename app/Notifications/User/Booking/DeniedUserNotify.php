<?php

namespace App\Notifications\User\Booking;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Http\Controllers\LineIntegrationController;
use App\Channels\LineChannel;

class DeniedUserNotify extends Notification
{
    use Queueable;

    protected Booking $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
        $bookedFrom = $this->booking->booked_from;
        $bookedTo = $this->booking->booked_to;

        $mail = new MailMessage()
            ->subject('ผลการจองห้อง - ' . $this->booking->booking_name . ' ' . $bookedFrom->format('Y-m-d') . ' - ' . (\App\Models\AppSetting::getSetting('name') ?? config('app.name', 'PSC-MusicWeb Project')))
            ->greeting('เรียน คุณ' . $notifiable->name . ',')
            ->line('ขณะนี้ผู้ดูและระบบได้ตรวจสอบการจองห้องของคุณแล้ว รายละเอียดดังนี้:')
            ->line('- ชื่อการจอง: ' . $this->booking->booking_name)
            ->line('- วันที่จอง: ' . $bookedFrom->locale('th')->isoFormat('DD MMMM YYYY'))
            ->line('- เวลา: ' . $bookedFrom->format('H:i') . ' - ' . $bookedTo->format('H:i'))
            ->line('- ผู้เข้าร่วม: ' . (!empty($this->booking->parseAttendeeforDisplay()) ? implode(', ', $this->booking->parseAttendeeforDisplay()) : 'ไม่ระบุผู้เข้าร่วม'))
            ->line('');

        if ($this->booking->approval_comment) {
            $mail->line('แต่เนื่องจาก ' . $this->booking->approval_comment . " จึงไม่สามารถอนุมัติการจองห้องของคุณได้ในครั้งนี้")
                 ->line('');
        } else {
            $mail->line('แต่ก็ขอแสดงความเสียที่ไม่สามารถอนุมัติการจองห้องของคุณได้ในครั้งนี้')
                 ->line('');
        }

        $mail->line('หากคุณมีคำถามหรือต้องการข้อมูลเพิ่มเติม กรุณาติดต่อผู้อนุมัติ ( ' . $this->booking->approvalPerson->name_title . $this->booking->approvalPerson->name . ' ' . $this->booking->approvalPerson->surname . ' )')
            ->line('ขอบคุณครับ/ค่ะ');

        return $mail;
    }

    public function toLineIntegration(object $notifiable): string {
        // Checking their LINE ID
        if ($notifiable->line_id === null) {
            return 'No Line User ID';
        }

        // Importing Line Flex Message Template from JSON file
        $jStr = file_get_contents(app_path('line/flex_messages/booking/user_denied.json'));
        $fTem = json_decode($jStr, true);

        // If current host aren't localhost, change the image from public-hosted image for placeholder to self-hosted one
        if (!in_array(request()->getHost(), ['localhost', '127.0.0.1'])) {
            $fTem['hero']['url'] = asset('assets/image/line/booking_denied.png');
        }


        // Replace placeholders with real data
        if ($this->booking->approval_comment) {
            $fTem['body']['contents'][1]['contents'][0]['text'] = 'การจองของคุณปฎิเสธโดย ' . $this->booking->approvalPerson->name_title . $this->booking->approvalPerson->name . ' ' . $this->booking->approvalPerson->surname . ' ด้วยเหตุผล: ' . $this->booking->approval_comment . ', หากต้องการข้อมูลเพิ่มเติมหรือโต้แย้งการอนุมัตินี้ กรุณาติดต่อผู้ดูแลระบบ';
        } else {
            $fTem['body']['contents'][1]['contents'][0]['text'] = 'การจองของคุณปฎิเสธโดย ' . $this->booking->approvalPerson->name_title . $this->booking->approvalPerson->name . ' ' . $this->booking->approvalPerson->surname . ', หากต้องการข้อมูลเพิ่มเติมหรือโต้แย้งการอนุมัตินี้ กรุณาติดต่อผู้ดูแลระบบ';
        }

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

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'ผลการจองห้อง',
            'message' => 'การจองห้องของคุณไม่ผ่านการอนุมัติ กรุณาตรวจสอบอีเมลหรือช่องทางแจ้งเตือนอื่นๆ เพื่อดูรายละเอียดเพิ่มเติม',
            'type' => 'warning'
        ]);
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
