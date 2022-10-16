<?php
/**
 * VerifyDomainSSLDataCompleted event
 *
 * @author: tuanha
 * @last-mod: 13-Jan-2021
 */
namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class VerifyDomainSSLDataCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \Bkstar123\BksCMS\AdminPanel\Admin
     */
    public $user;

    /**
     * @var integer
     */
    public $chunkCount;

    /**
     * Create a new event instance.
     *
     * @param $user \Bkstar123\BksCMS\AdminPanel\Admin
     * @param $chunkCount integer
     * @return void
     */
    public function __construct($user, $chunkCount)
    {
        $this->user = $user;
        $this->chunkCount = $chunkCount;
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
            'count'     => $this->chunkCount
        ];
    }
}
