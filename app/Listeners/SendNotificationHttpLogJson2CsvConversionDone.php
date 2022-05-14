<?php
/**
 * SendNotificationHttpLogJson2CsvConversionDone listener
 *
 * @author: tuanha
 * @last-mod: 13-May-2022
 */
namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\HttpLogJson2CsvConversionDone;
use App\Notifications\HttpLogJson2CsvNotification;

class SendNotificationHttpLogJson2CsvConversionDone
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
     * @param  HttpLogJson2CsvConversionDone  $event
     * @return void
     */
    public function handle(HttpLogJson2CsvConversionDone $event)
    {
        $event->user->notify(new HttpLogJson2CsvNotification($event));
    }
}
