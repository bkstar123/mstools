<?php
/**
 * HttpLogJson2CsvNotification Notification
 *
 * @author: tuanha
 * @last-mod: 13-May-2022
 */
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;

class HttpLogJson2CsvNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \App\Events\HttpLogJson2CsvConversionDone
     */
    protected $payload;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return empty($notifiable->profile->slack_webhook_url) ? [] : ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->success()
            ->from(config('app.name'))
            ->content('A task from MSTool has been completed')
            ->attachment(function ($attachment) {
                $attachment->fields([
                    'Task' => 'Convert .Net Core HTTP Log from JSON to CSV',
                    'Initiated By' => $this->payload->user->email,
                ]);
            });
    }
}
