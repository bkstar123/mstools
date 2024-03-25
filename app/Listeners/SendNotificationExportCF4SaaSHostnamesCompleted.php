<?php

namespace App\Listeners;

use App\Events\ExportCF4SaaSHostnamesCompleted;
use App\Notifications\ExportCF4SaaSHostnamesNotification;


class SendNotificationExportCF4SaaSHostnamesCompleted
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
     * @param  ExportCF4SaaSHostnamesCompleted  $event
     * @return void
     */
    public function handle(ExportCF4SaaSHostnamesCompleted $event)
    {
        $event->user->notify(new ExportCF4SaaSHostnamesNotification($event));
    }
}
