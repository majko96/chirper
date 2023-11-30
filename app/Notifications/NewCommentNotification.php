<?php

namespace App\Notifications;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
{

    public $chirp;
    public $user;

    public function __construct(
        Chirp $chirp,
        User $user
    ) {
        $this->chirp = $chirp;
        $this->user = $user;
    }
    public function toDatabase($notifiable)
    {
        return [
            'message' => 'You have a new comment on your post!',
            'post_id' => $this->chirp->id,
            'user_id' => $this->user->id,
        ];
    }

    public function toArray($notifiable)
    {
        return [
            // Additional data for the toArray method if needed
        ];
    }

    public function via($notifiable)
    {
        return [DatabaseChannel::class];
    }
}
