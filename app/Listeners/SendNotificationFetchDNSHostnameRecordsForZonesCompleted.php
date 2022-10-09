<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\FetchDNSHostnameRecordsForZonesCompleted;
use App\Notifications\CFDNSHostnameEntriesFetchedNotification;

class SendNotificationFetchDNSHostnameRecordsForZonesCompleted
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
     * @param  FetchDNSHostnameRecordsForZonesCompleted  $event
     * @return void
     */
    public function handle(FetchDNSHostnameRecordsForZonesCompleted $event)
    {
        $event->user->notify(new CFDNSHostnameEntriesFetchedNotification($event));
    }
}
