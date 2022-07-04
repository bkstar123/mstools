<?php
/**
 * CheckDNSCompleted event
 *
 * @author: tuanha
 * @last-mod: 04-July-2022
 */
namespace App\Events;

use App\Report;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CheckDNSCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var array
     */
    public $domains;

    /**
     * @var \App\Report
     */
    public $report;

    /**
     * @var \Bkstar123\BksCMS\AdminPanel\Admin
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Report $report, $domains, $user)
    {
        $this->report = $report;
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
        return 'check-dns.completed';
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
