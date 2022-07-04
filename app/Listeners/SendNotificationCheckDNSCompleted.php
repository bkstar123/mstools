<?php
/**
 * SendNotificationCheckDNSCompleted listener
 *
 * @author: tuanha
 * @last-mod: 04-July-2022
 */
namespace App\Listeners;

use App\Events\CheckDNSCompleted;
use App\Notifications\CheckDNSNotification;

class SendNotificationCheckDNSCompleted
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
     * @param  CheckDNSCompleted  $event
     * @return void
     */
    public function handle(CheckDNSCompleted $event)
    {
        $event->user->notify(new CheckDNSNotification($event));
    }
}
