<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\FetchCFDNSTargetsForHostnamesCompleted;
use App\Notifications\CFDNSTargetsForHostnamesFetchedNotification;

class SendNotificationFetchCFDNSTargetsForHostnamesCompleted
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
     * @param  FetchCFDNSTargetsForHostnamesCompleted  $event
     * @return void
     */
    public function handle(FetchCFDNSTargetsForHostnamesCompleted $event)
    {
        $event->user->notify(new CFDNSTargetsForHostnamesFetchedNotification($event));
    }
}
