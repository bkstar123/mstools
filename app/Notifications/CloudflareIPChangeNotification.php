<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;

class CloudflareIPChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    public $addedIPs;

    /**
     * @var string
     */
    public $removedIPs;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($addedIPs, $removedIPs)
    {
        $this->addedIPs = $addedIPs;
        $this->removedIPs = $removedIPs;
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
            ->content('Cloudflare IP change detected')
            ->attachment(function ($attachment) {
                $attachment->fields([
                    'Added IPs' => $this->addedIPs,
                    'Removed IPs' => $this->removedIPs,
                ]);
            });
    }
}
