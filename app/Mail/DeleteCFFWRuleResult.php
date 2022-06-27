<?php

namespace App\Mail;

use App\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteCFFWRuleResult extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array
     */
    public $zones;

    /**
     * @var \App\Report
     */
    protected $report;

    /**
     * @var string
     */
    public $ruleDescription;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Report $report, $zones, $ruleDescription)
    {
        $this->report = $report;
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
        if (Storage::disk($this->report->disk)->exists($this->report->path)) {
            return  $this->view('emails.firewall.deleterule')
                         ->subject('Delete Cloudflare firewall rule for multiple zones')
                         ->attach(Storage::disk($this->report->disk)->path($this->report->path), [
                            'as' => 'cf_zone_firewall_rule_delete_report.csv',
                            'mime' => 'text/csv'
                        ]);
        }
    }
}
