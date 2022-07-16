<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\GetPingdomChecksDetailsCompleted;
use App\Notifications\GetDetailsPingdomChecksNotification;

class SendNotificationGetPingdomChecksDetailsCompleted
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
     * @param  GetPingdomChecksDetailsCompleted  $event
     * @return void
     */
    public function handle(GetPingdomChecksDetailsCompleted $event)
    {
        $event->user->notify(new GetDetailsPingdomChecksNotification($event));
    }
}
