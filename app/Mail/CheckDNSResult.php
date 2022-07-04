<?php

namespace App\Mail;

use App\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckDNSResult extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array
     */
    public $domains;

    /**
     * @var \App\Report
     */
    protected $report;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Report $report, $domains)
    {
        $this->report = $report;
        $this->domains = $domains;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (Storage::disk($this->report->disk)->exists($this->report->path)) {
            return  $this->view('emails.dns.dnsrecords')
                         ->subject('Check DNS Records')
                         ->attach(Storage::disk($this->report->disk)->path($this->report->path), [
                            'as' => 'check_dns_records.csv',
                            'mime' => 'text/csv'
                        ]);
        }
    }
}
