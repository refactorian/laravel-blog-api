<?php

namespace App\Notifications;

use App\Models\Follow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserFollowedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Follow $follow) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New follower')
            ->line($this->follow->follower->name.' is now following you.')
            ->action('View Profile', url('/users/'.$this->follow->follower->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'follow_id' => $this->follow->id,
            'follower_id' => $this->follow->follower_id,
            'following_id' => $this->follow->following_id,
            'message' => $this->follow->follower->name.' is now following you',
        ];
    }
}
