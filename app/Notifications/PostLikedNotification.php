<?php

namespace App\Notifications;

use App\Models\Like;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PostLikedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Like $like) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'like_id' => $this->like->id,
            'post_id' => $this->like->post_id,
            'user_id' => $this->like->user_id,
            'message' => $this->like->user->name.' liked your post "'.$this->like->post->title.'"',
        ];
    }
}
