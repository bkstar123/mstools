<?php
/**
 * HttpLogJson2CsvNotification Notification
 *
 * @author: tuanha
 * @last-mod: 13-May-2022
 */
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Mail\HttpLogJson2CsvResult;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        return empty($notifiable->profile->slack_webhook_url) ? ['mail'] : ['mail', 'slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \App\Mail\HttpLogJson2CsvResult
     */
    public function toMail($notifiable)
    {
        return (new HttpLogJson2CsvResult($this->payload->outputTempLocation))
               ->to($notifiable->email);
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

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
