<?php

namespace App\Jobs;

use Exception;
use App\Report;
use Carbon\Carbon;
use App\Events\JobFailing;
use Illuminate\Bus\Queueable;
use App\Events\CheckDNSCompleted;
use App\Http\Components\DNSQueryable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Components\GenerateCustomUniqueString;

class CheckAAndCnameDnsRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GenerateCustomUniqueString, DNSQueryable;

    /**
     * @var array
     */
    protected $domains;

    /**
     * @var \Bkstar123\BksCMS\AdminPanel\Admin
     */
    protected $user;

    /**
     * The number of seconds the job can run before timing out
     * must be on several seconds less than the queue connection's retry_after defined in the config/queue.php
     *
     * @var int
     */
    public $timeout = 1190;

    /**
     * Create a new job instance.
     *
     * @param $domains array
     * @param $user \Bkstar123\BksCMS\AdminPanel\Admin
     * @return void
     */
    public function __construct($domains, $user)
    {
        $this->domains = $domains;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $outputFileLocation = [
            'disk' => config('mstools.report.disk'),
            'path' => config('mstools.report.directory').DIRECTORY_SEPARATOR.$this->generateUniqueString().DIRECTORY_SEPARATOR.$this->generateUniqueString('.csv')
        ];
        Storage::disk($outputFileLocation['disk'])->makeDirectory(dirname($outputFileLocation['path']));
        $fop = fopen(Storage::disk($outputFileLocation['disk'])->path($outputFileLocation['path']), 'w');
        fputcsv($fop, [
            'URL',
            'A',
            'CNAME'
        ]);
        foreach ($this->domains as $domain) {
            $domain = idn_to_ascii(trim($domain), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $dnsRecords = $this->getDNSRecords($domain);
            fputcsv($fop, [
                $domain,
                implode(',', $dnsRecords['A']),
                implode(',', $dnsRecords['CNAME'])
            ]);
        }
        fclose($fop);
        $report = Report::create([
            'name'     => 'DNS A and CNAME records for domains ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id' => $this->user->id,
            'disk'     => $outputFileLocation['disk'],
            'path'     => $outputFileLocation['path'],
            'mime'     => 'text/csv'
        ]);
        CheckDNSCompleted::dispatch($report, $this->domains, $this->user);
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        JobFailing::dispatch($this->user);
    }
}
