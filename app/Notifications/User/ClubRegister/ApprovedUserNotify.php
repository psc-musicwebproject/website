<?php

namespace App\Notifications\User\ClubRegister;

use Illuminate\Bus\Queueable;
use App\Http\Controllers\LineIntegrationController;
use App\Channels\LineChannel;
use Illuminate\Notifications\Messages\MailMessage;
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

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ผลการสมัครสมาชิกชมรม ฯ - ' . (\App\Models\AppSetting::getSetting('name') ?? config('app.name', 'PSC-MusicWeb Project')))
            ->greeting('เรียน คุณ' . $this->clubMember->user->name . ',')
            ->line('ก่อนอื่น ต้องขอขอบคุณที่คุณได้ให้ความสนใจและสมัครเข้าร่วมชมรมดนตรีของ ')
            ->line('หรือสามารถตรวจสอบสถานะการสมัครสมาชิกได้ที่หน้าชมรมดนตรี ตามลิงก์ด้านล่างนี้')
            ->action('ตรวจสอบสถานะสมาชิก', route('dash.club.register'))
            ->line('ขอบคุณครับ/ค่ะ');
    }
}
