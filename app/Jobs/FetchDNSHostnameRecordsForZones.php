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
use App\Events\FetchDNSHostnameRecordsForZonesCompleted;

class FetchDNSHostnameRecordsForZones implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GenerateCustomUniqueString;

    /**
     * @var array
     */
    protected $zones;

    /**
     * @var \Bkstar123\BksCMS\AdminPanel\Admin
     */
    protected $user;

    /**
     * @var string
     */
    protected $onlyDNSName;

    /**
     * @var string
     */
    protected $onlyProd;

    /**
     * @var string
     */
    protected $onlyProxied;

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
     * @param $zones array
     * @param $user \Bkstar123\BksCMS\AdminPanel\Admin
     * @param $onlyProd string ('on' | null)
     * @param $onlyProxied string ('on' | null)
     * @return void
     */
    public function __construct($zones, $user, $onlyProd, $onlyProxied)
    {
        $this->zones = $zones;
        $this->user = $user;
        $this->onlyProd = (bool) $onlyProd;
        $this->onlyProxied = !is_null($onlyProxied) ? (bool) $onlyProxied : $onlyProxied;
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
        foreach ($this->zones as $zone) {
            $zone = idn_to_ascii(trim($zone), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $zoneID = $zoneMgmt->getZoneID($zone);
            if (empty($zoneID)) {
                fputcsv($fop, [
                    $zone,
                    '',
                    '',
                    "Failed to check this zone's data on Cloudflare"
                ]);
                continue;
            }
            $entries = array_merge($entries, $zoneMgmt->getZoneSubDomains($zoneMgmt->getZoneID($zone), null, false, $this->onlyProd, null, $this->onlyProxied));
        }
        fwrite($fop, implode("\n", $entries) . "\n");
        fclose($fop);
        $report = Report::create([
            'name'     => 'Cloudflare DNS hostname entries for zones ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id' => $this->user->id,
            'disk'     => $outputFileLocation['disk'],
            'path'     => $outputFileLocation['path'],
            'mime'     => 'text/csv'
        ]);
        FetchDNSHostnameRecordsForZonesCompleted::dispatch($this->user);
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
