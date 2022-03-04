<?php
/**
 * SendNotificationDeleteCFFWRuleCompleted listener
 *
 * @author: tuanha
 * @last-mod: 04-Mar-2022
 */
namespace App\Listeners;

use App\Events\DeleteCFFWRuleCompleted;
use App\Notifications\DeleteCFFWRuleCompletedNotification;

class SendNotificationDeleteCFFWRuleCompleted
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
     * @param  DeleteCFFWRuleCompleted  $event
     * @return void
     */
    public function handle(DeleteCFFWRuleCompleted $event)
    {
        $event->user->notify(new DeleteCFFWRuleCompletedNotification($event));
    }
}
