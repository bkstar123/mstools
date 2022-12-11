<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CFJDCloudZoneChangedNotify extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $addedZones;

    /**
     * @var string
     */
    public $removedZones;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($addedZones, $removedZones)
    {
        $this->addedZones = $addedZones;
        $this->removedZones = $removedZones;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.cfjdcloudzonechanged')
                    ->subject('Cloudflare China Network Zones - change detected');
    }
}
