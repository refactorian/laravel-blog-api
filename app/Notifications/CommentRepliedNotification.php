<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentRepliedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Comment $comment, public Comment $parent) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Someone replied to your comment')
            ->line($this->comment->user->name.' replied to your comment on "'.$this->comment->post->title.'"')
            ->line('"'.$this->comment->body.'"')
            ->action('View Post', url('/posts/'.$this->comment->post->slug));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'post_id' => $this->comment->post_id,
            'user_id' => $this->comment->user_id,
            'parent_id' => $this->parent->id,
            'message' => $this->comment->user->name.' replied to your comment',
        ];
    }
}
