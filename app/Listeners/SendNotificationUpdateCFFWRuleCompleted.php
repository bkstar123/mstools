<?php
/**
 * SendNotificationUpdateCFFWRuleCompleted listener
 *
 * @author: tuanha
 * @last-mod: 03-Mar-2022
 */
namespace App\Listeners;

use App\Events\UpdateCFFWRuleCompleted;
use App\Notifications\UpdateCFFWRuleCompletedNotification;

class SendNotificationUpdateCFFWRuleCompleted
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
     * @param  UpdateCFFWRuleCompleted  $event
     * @return void
     */
    public function handle(UpdateCFFWRuleCompleted $event)
    {
        $event->user->notify(new UpdateCFFWRuleCompletedNotification($event));
    }
}
