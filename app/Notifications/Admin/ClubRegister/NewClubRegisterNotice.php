<?php

namespace App\Notifications\Admin\ClubRegister;

use App\Channels\LineChannel;
use Illuminate\Bus\Queueable;
use App\Http\Controllers\LineIntegrationController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\ClubMember;

class NewClubRegisterNotice extends Notification
{
    use Queueable;

    protected $clubMember;

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

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('สมาชิกใหม่รอการอนุมัติ - ' . $this->clubMember->user->name_title . $this->clubMember->user->name . ' ' . $this->clubMember->user->surname  . ' - ' . (\App\Models\AppSetting::getSetting('name') ?? config('app.name', 'PSC-MusicWeb Project')))
            ->greeting('เรียน คุณ' . $notifiable->name . ',')
            ->line('คุณมีสมาชิกใหม่รอการอนุมัติ')
            ->line('โปรดตรวจสอบและอนุมัติสมาชิกผ่านลิงก์ด้านล่างนี้')
            ->action('ตรวจสอบสถานะสมาชิก', route('admin.club.approve.detail', ['id' => $this->clubMember->member_id]))
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

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => 'มีการสมัครสมาชิกใหม่โดย ' . $this->clubMember->user->name . ' ' . $this->clubMember->user->surname,
            'type' => 'new_club_member',
            'club_member_id' => $this->clubMember->id,
        ]);
    }

    public function toLineIntegration(object $notifiable): string
    {
        // Importing Line Flex Message Template from JSON
        if ($notifiable->line_id == null) {
            return "No Line User ID";
        }

        $jStr = file_get_contents(app_path('line/flex_messages/club_register/admin/sent_alert.json'));
        $fTem = json_decode($jStr, true);

        // Replace Placeholder data to real data
        $fTem['body']['contents'][2]['contents'][0]['contents'][1]['text'] = $this->clubMember->user->name_title . $this->clubMember->user->name . ' ' . $this->clubMember->user->surname;
        $fTem['body']['contents'][2]['contents'][1]['contents'][1]['text'] = $this->clubMember->user->major;

        $fTem['footer']['contents'][0]['action']['uri'] = route('admin.club.approve.detail', ['id' => $this->clubMember->member_id]);

        $lineController = new LineIntegrationController();
        return $lineController->pushFlexMessage($notifiable->line_id, "แจ้งเตือนการสมัครสมาชิกใหม่", $fTem);
    }
}
