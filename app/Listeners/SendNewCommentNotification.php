<?php

namespace App\Listeners;

use App\Events\NewCommentAdded;
use App\Notifications\NewCommentNotification;

class SendNewCommentNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewCommentAdded $event): void
    {
        $postOwner = $event->post->user;
        $user = $event->user;

        $postOwner->notify(new NewCommentNotification($event->post, $user));
    }
}
