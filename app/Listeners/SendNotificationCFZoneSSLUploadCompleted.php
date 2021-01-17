<?php
/**
 * SendNotificationCFZoneSSLUploadCompleted listener
 *
 * @author: tuanha
 * @last-mod: 17-Jan-2021
 */
namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\UploadCustomCertificateToCloudflareCompleted;
use App\Notifications\UploadCustomCertificateToCloudflareNotification;

class SendNotificationCFZoneSSLUploadCompleted
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
     * @param  UploadCustomCertificateToCloudflareCompleted  $event
     * @return void
     */
    public function handle(UploadCustomCertificateToCloudflareCompleted $event)
    {
        $event->user->notify(new UploadCustomCertificateToCloudflareNotification($event));
    }
}
