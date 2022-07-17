<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\GetPingdomChecksAvgSummaryCompleted;
use App\Notifications\PingdomChecksAvgSummaryNotification;

class SendNotificationChecksAvgSumCompleted
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
     * @param  GetPingdomChecksAvgSummaryCompleted  $event
     * @return void
     */
    public function handle(GetPingdomChecksAvgSummaryCompleted $event)
    {
        $event->user->notify(new PingdomChecksAvgSummaryNotification($event));
    }
}
