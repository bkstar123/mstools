<?php
/**
 * SendNotificationCreateCFFWRuleCompleted listener
 *
 * @author: tuanha
 * @last-mod: 02-Mar-2022
 */
namespace App\Listeners;

use App\Events\CreateCFFWRuleCompleted;
use App\Notifications\CreateCFFWRuleCompletedNotification;

class SendNotificationCreateCFFWRuleCompleted
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
     * @param  CreateCFFWRuleCompleted  $event
     * @return void
     */
    public function handle(CreateCFFWRuleCompleted $event)
    {
        $event->user->notify(new CreateCFFWRuleCompletedNotification($event));
    }
}
