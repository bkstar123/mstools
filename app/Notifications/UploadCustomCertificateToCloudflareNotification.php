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
use App\Mail\UploadCustomCertificateToCloudflareResult;

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
        return empty($notifiable->profile->slack_webhook_url) ? ['mail'] : ['mail', 'slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new UploadCustomCertificateToCloudflareResult($this->payload->report, $this->payload->zones))
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
                               'Task' => 'Upload certificate for Cloudflare zones',
                               'Number of zones' => count($this->payload->zones),
                               'First zone in the list' => head($this->payload->zones),
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
