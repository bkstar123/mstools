<?php

namespace App\Jobs;

use Exception;
use App\Report;
use Carbon\Carbon;
use App\Events\JobFailing;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Components\GenerateCustomUniqueString;
use App\Events\FetchCFDNSTargetsForHostnamesCompleted;

class FetchCFDNSTargetsForHostnames implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GenerateCustomUniqueString;

    /**
     * @var array
     */
    protected $hostnames;

    /**
     * @var \Bkstar123\BksCMS\AdminPanel\Admin
     */
    protected $user;

    /**
     * @var integer
     */
    protected $chunkCount;

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
     * @param $hostnames array
     * @param $user \Bkstar123\BksCMS\AdminPanel\Admin
     * @param $chunkCount integer
     * @return void
     */
    public function __construct($hostnames, $user, $chunkCount)
    {
        $this->hostnames = $hostnames;
        $this->user = $user;
        $this->chunkCount = $chunkCount;
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
            'Hostname',
            'Cloudflare DNS Type',
            'Target',
            'Note'
        ]);
        $zoneMgmt = resolve('zoneMgmt');
        $entries = [];
        foreach ($this->hostnames as $hostname) {
            $hostname = idn_to_ascii(trim($hostname), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $zone = array_first(detectCFZonesFromHostnames((array) $hostname));
            $zoneID = $zoneMgmt->getZoneID($zone);
            if (empty($zoneID)) {
                fputcsv($fop, [
                    $hostname,
                    '',
                    '',
                    "Not found any zone on Cloudflare associated with this hostname"
                ]);
                continue;
            }
            $entries = array_merge($entries, $zoneMgmt->getZoneSubDomains($zoneID, $hostname, false, false, null, null));
        }
        fwrite($fop, implode("\n", $entries) . "\n");
        fclose($fop);
        $report = Report::create([
            'name'     => 'Cloudflare DNS targets for zones ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id' => $this->user->id,
            'disk'     => $outputFileLocation['disk'],
            'path'     => $outputFileLocation['path'],
            'mime'     => 'text/csv'
        ]);
        FetchCFDNSTargetsForHostnamesCompleted::dispatch($this->user, $this->chunkCount);
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
