<?php
/**
 * SendNotificationPingdomCheckExportCompletion listener
 *
 * @author: tuanha
 * @last-mod: 23-Oct-2021
 */
namespace App\Listeners;

use App\Events\ExportPingdomChecksCompleted;
use App\Notifications\ExportPingdomChecksNotification;

class SendNotificationPingdomCheckExportCompletion
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
     * @param  ExportPingdomChecksCompleted  $event
     * @return void
     */
    public function handle(ExportPingdomChecksCompleted $event)
    {
        $event->user->notify(new ExportPingdomChecksNotification($event));
    }
}
