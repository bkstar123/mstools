<?php
/**
 * ExportPingdomChecksCompleted event
 *
 * @author: tuanha
 * @last-mod: 23-Oct-2021
 */
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ExportPingdomChecksCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var array
     */
    public $outputFileLocation;

    /**
     * @var \Bkstar123\BksCMS\AdminPanel\Admin
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($outputFileLocation, $user)
    {
        $this->outputFileLocation = $outputFileLocation;
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
        return 'export-pingdom-check.completed';
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
            'filepath' => $this->outputFileLocation['path'],
            'disk' => $this->outputFileLocation['disk']
        ];
    }
}
