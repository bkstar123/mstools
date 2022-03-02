<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateCFFWRuleResult extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array
     */
    public $zones;

    /**
     * @var base64-encoded binary data
     */
    protected $attachment;

    /**
     * @var string
     */
    public $ruleDescription;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($attachment, $zones, $ruleDescription)
    {
        $this->attachment = $attachment;
        $this->zones = $zones;
        $this->ruleDescription = $ruleDescription;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.firewall.createrule')
                    ->subject('Create Cloudflare firewall rule for multiple zones')
                    ->attachData(base64_decode($this->attachment), 'cf_zone_firewall_rule_create_report.xlsx');
    }
}
