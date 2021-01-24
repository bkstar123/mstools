<?php
/**
 * SendNotificationDomainSSLDataCompletion listener
 *
 * @author: tuanha
 * @last-mod: 13-Jan-2021
 */
namespace App\Listeners;

use App\Events\VerifyDomainSSLDataCompleted;
use App\Notifications\VerifyDomainSSLDataNotification;

class SendNotificationDomainSSLDataCompletion
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
     * @param  VerifyDomainSSLDataCompleted  $event
     * @return void
     */
    public function handle(VerifyDomainSSLDataCompleted $event)
    {
        $event->user->notify(new VerifyDomainSSLDataNotification($event));
    }
}
