<?php

namespace App\Notifications\User\ClubRegister;

use Illuminate\Bus\Queueable;
use App\Http\Controllers\LineIntegrationController;
use App\Channels\LineChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\ClubMember;

class ApprovedUserNotify extends Notification
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
        return ['mail', LineChannel::class, 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('ผลการสมัครสมาชิกชมรม ฯ - ' . (\App\Models\AppSetting::getSetting('name') ?? config('app.name', 'PSC-MusicWeb Project')))
            ->greeting('เรียน คุณ' . $this->clubMember->user->name . ',')
            ->line('ก่อนอื่น ต้องขอแสดงความยินดีที่การสมัครสมาชิกชมรมดนตรีของคุณได้รับการอนุมัติแล้ว')
            ->line('โดยคุณสามารถเข้าร่วมกิจกรรมต่าง ๆ ที่ชมรมดนตรีจัดขึ้น รวมถึงการใช้สิทธิประโยชน์ต่าง ๆ ที่สมาชิกชมรมจะได้รับในอนาคต')
            ->line('');

        $mail->line('ผู้อนุมัติ: ' . $this->clubMember->approvalPerson->name_title . $this->clubMember->approvalPerson->name . ' ' . $this->clubMember->approvalPerson->surname);

        if ($this->clubMember->approval_comment) {
            $mail->line('ความคิดเห็นเพิ่มเติม: ' . $this->clubMember->approval_comment);
        }

        $mail->line('วันที่อนุมัติ: ' . $this->clubMember->approval_time->format('d/m/Y H:i'))
            ->line('')
            ->line('หวังเป็นอย่างยิ่งว่าคุณจะมีช่วงเวลาที่ดีและได้รับประสบการณ์ที่น่าจดจำในฐานะสมาชิกชมรมดนตรีของเรา');

        return $mail;
    }

    public function toLineIntegration(object $notifiable): string {
        // Check if user has linked Line account
        if ($notifiable->line_id === null) {
            return 'No Line User ID';
        }

        // Importing Template from JSON file
        $jStr = file_get_contents(app_path('line/flex_messages/club_register/user_approved.json'));
        $fTem = json_decode($jStr, true);

        // Customize Template with dynamic data
        $fTem['body']['contents'][2]['contents'][0]['contents'][1]['text'] = $this->clubMember->user->name_title . $this->clubMember->user->name . ' ' . $this->clubMember->user->surname;
        $fTem['body']['contents'][2]['contents'][1]['contents'][1]['text'] = $this->clubMember->user->major;
        $fTem['body']['contents'][2]['contents'][2]['contents'][1]['text'] = $this->clubMember->approvalPerson->name_title . $this->clubMember->approvalPerson->name . ' ' . $this->clubMember->approvalPerson->surname;

        // If approval comment exists, add new content in $fTem['body']['contents'][2]['contents'][x] with same style as other contents
        if ($this->clubMember->approval_comment) {
            $fTem['body']['contents'][2]['contents'][] = [
                "type" => "box",
                "layout" => "baseline",
                "spacing" => "lg",
                "contents" => [
                    [
                        "type" => "text",
                        "text" => "หมายเหตุ",
                        "color" => "#aaaaaa",
                        "size" => "sm",
                        "flex" => 1,
                        "wrap" => true,
                        "margin" => "none"
                    ],
                    [
                        "type" => "text",
                        "text" => $this->clubMember->approval_comment,
                        "wrap" => true,
                        "color" => "#666666",
                        "size" => "sm",
                        "flex" => 5
                    ]
                ]
            ];
        }

        $LineController = new LineIntegrationController();
        return $LineController->pushFlexMessage($notifiable->line_id, 'ผลการสมัครสมาชิกชมรมดนตรี', $fTem);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'ผลการสมัครสมาชิกชมรมดนตรี',
            'message' => 'ผลการสมัครสมาชิกชมรมดนตรีของคุณได้รับการพิจารณาแล้ว กรุณาตรวจสอบรายละเอียดเพิ่มเติม',
            'action_url' => route('dash.club.register'),
            'type' => 'success'
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
