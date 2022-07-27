<?php

namespace App\Listeners;

use App\Events\VerifyExistenceCFFWRuleCompleted;
use App\Notifications\VerifyExistenceCFFWRuleCompletedNotification;

class SendNotificationVerifyExistenceCFFWRuleCompleted
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  VerifyExistenceCFFWRuleCompleted  $event
     * @return void
     */
    public function handle(VerifyExistenceCFFWRuleCompleted $event)
    {
        $event->user->notify(new VerifyExistenceCFFWRuleCompletedNotification($event));
    }
}
