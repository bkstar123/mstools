<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;

class CloudflareJDCloudZoneChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    public $addedZones;

    /**
     * @var string
     */
    public $removedZones;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($addedZones, $removedZones)
    {
        $this->addedZones = $addedZones;
        $this->removedZones = $removedZones;
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
            ->content('List of China network zones on Cloudflare - change detection')
            ->attachment(function ($attachment) {
                $attachment->fields([
                    'Added Zones' => $this->addedZones,
                    'Removed Zones' => $this->removedZones,
                ]);
            });
    }
}
