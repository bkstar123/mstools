<?php
/**
 * VerifyDomainSSLDataCompleted event
 *
 * @author: tuanha
 * @last-mod: 13-Jan-2021
 */
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class VerifyDomainSSLDataCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var array
     */
    public $domains;

    /**
     * @var base64-encoded binary data
     */
    public $attachment;

    /**
     * @var \Bkstar123\BksCMS\AdminPanel\Admin
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($attachment, $domains, $user)
    {
        $this->attachment = base64_encode($attachment);
        $this->domains = $domains;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user-'. $this->user->id);
    }

    /**
     * Give an alias name to the event
     *
     * @return Channel|array
     */
    public function broadcastAs()
    {
        return 'verify-domain-ssldata.completed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'requestor' => $this->user->email,
            'number_of_domains' => count($this->domains)
        ];
    }
}
