<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Mail\VerifyDomainSSLDataResult;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;

class VerifyDomainSSLDataNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \App\Events\VerifyDomainSSLDataCompleted
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
     * @return \App\Mail\VerifyDomainSSLDataResult
     */
    public function toMail($notifiable)
    {
        return (new VerifyDomainSSLDataResult($this->payload->attachment, $this->payload->domains))
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
                               'Task' => 'Verify Domains\' SSL Data',
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
