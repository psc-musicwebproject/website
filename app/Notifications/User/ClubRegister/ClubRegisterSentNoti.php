<?php

namespace App\Notifications\User\ClubRegister;

use Illuminate\Bus\Queueable;
use App\Http\Controllers\LineIntegrationController;
use App\Channels\LineChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ClubMember;

class ClubRegisterSentNoti extends Notification
{
    use Queueable;

    protected ClubMember $clubMember;

    /**
     * Create a new notification instance.
     */
    public function __construct(ClubMember $clubMember)
    {
        $this->clubMember = $clubMember;
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
            ->subject('สมัครสมาชิกชมรมดนตรีสำเร็จ - รอการอนุมัติ - ' . (\App\Models\AppSetting::getSetting('name') ?? config('app.name', 'PSC-MusicWeb Project')))
            ->greeting('เรียน คุณ' . $this->clubMember->user->name . ',')
            ->line('คุณได้สมัครสมาชิกชมรมดนตรีเรียบร้อยแล้ว กรุณารอการอนุมัติจากผู้ดูแลระบบ ระบบจะแจ้งเตือนเมื่อการสมัครสมาชิกของคุณได้รับการอนุมัติ')
            ->line('หรือสามารถตรวจสอบสถานะการสมัครสมาชิกได้ที่หน้าชมรมดนตรี ตามลิงก์ด้านล่างนี้')
            ->action('ตรวจสอบสถานะสมาชิก', route('dash.club.register'))
            ->line('ขอบคุณครับ/ค่ะ');
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

    public function toLineIntegration(object $notifiable): string
    {
        // Importing Line Flex Message Template from JSON file
        if ($notifiable->line_id == null) {
            return "No Line User ID";
        }

        $jStr = file_get_contents(app_path('line/flex_messages/club_register/sent_alert.json'));
        $fTem = json_decode($jStr, true);

        // Replace Placeholder data to real data
        $fTem['body']['contents'][2]['contents'][0]['contents'][1]['text'] = $this->clubMember->user->name_title . $this->clubMember->user->name . ' ' . $this->clubMember->user->surname;
        $fTem['body']['contents'][2]['contents'][1]['contents'][1]['text'] = $this->clubMember->user->major;

        $fTem['footer']['contents'][0]['action']['uri'] = route('dash.club.register');

        $lCon = new LineIntegrationController();
        return $lCon->pushFlexMessage($notifiable->line_id, "สมัครสมาชิกชมรมสำเร็จแล้ว", $fTem);
    }
}
