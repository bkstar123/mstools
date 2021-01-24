<?php
/**
 * SendNotificationCFZoneCustomSSLCompletion listener
 *
 * @author: tuanha
 * @last-mod: 10-Jan-2021
 */
namespace App\Listeners;

use App\Events\VerifyCFZoneCustomSSLCompleted;
use App\Notifications\VerifyCFZoneCustomSSLNotification;

class SendNotificationCFZoneCustomSSLCompletion
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
     * @param  VerifyCFZoneCustomSSLCompleted  $event
     * @return void
     */
    public function handle(VerifyCFZoneCustomSSLCompleted $event)
    {
        $event->user->notify(new VerifyCFZoneCustomSSLNotification($event));
    }
}
