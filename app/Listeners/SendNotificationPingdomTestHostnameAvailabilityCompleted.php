<?php

namespace App\Listeners;

use App\Events\PingdomTestHostnameAvailabilityCompleted;
use App\Notifications\PingdomTestHostnameAvailabilityNotification;

class SendNotificationPingdomTestHostnameAvailabilityCompleted
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
     * @param  PingdomTestHostnameAvailabilityCompleted  $event
     * @return void
     */
    public function handle(PingdomTestHostnameAvailabilityCompleted $event)
    {
        $event->user->notify(new PingdomTestHostnameAvailabilityNotification($event));
    }
}
