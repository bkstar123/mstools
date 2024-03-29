<?php
/**
 * UploadCustomCertificateToCloudflareNotification Notification
 *
 * @author: tuanha
 * @last-mod: 17-Jan-2021
 */
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;

class UploadCustomCertificateToCloudflareNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \App\Events\UploadCustomCertificateToCloudflareCompleted
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
                               'Task' => 'Upload certificate for Cloudflare zones',
                               'Initiated By' => $this->payload->user->email,
                           ]);
            });
    }
}
