<?php
/**
 * CreateCFFWRuleCompleted Event
 *
 * @author: tuanha
 * @last-mod: 02-Mar-2022
 */
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CreateCFFWRuleCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var array
     */
    public $zones;

    /**
     * @var base64-encoded binary data
     */
    public $attachment;

    /**
     * @var \Bkstar123\BksCMS\AdminPanel\Admin
     */
    public $user;

    /**
     * @var string
     */
    public $ruleDescription;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($attachment, $zones, $user, $ruleDescription)
    {
        $this->attachment = base64_encode($attachment);
        $this->zones = $zones;
        $this->user = $user;
        $this->ruleDescription = $ruleDescription;
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
        return 'create-cf-fwrule.completed';
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
            'number_of_zones' => count($this->zones),
            'rule_description' => $this->ruleDescription
        ];
    }
}
