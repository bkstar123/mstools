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
use App\Events\VerifyExistenceCFFWRuleCompleted;
use App\Http\Components\GenerateCustomUniqueString;

class VerifyExistenceCFFWRule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GenerateCustomUniqueString;

    /**
     * @var array
     */
    public $zones;

    /**
     * @var string
     */
    public $ruleDescription;

    /**
     * @var Bkstar123\BksCMS\AdminPanel\Admin
     */
    public $user;

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
     * @return void
     */
    public function __construct(array $zones, $ruleDescription, $user)
    {
        $this->zones = $zones;
        $this->ruleDescription = $ruleDescription;
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
            'Zone',
            'Rule Description',
            'Comment',
        ]);
        $zoneMgmt = resolve('zoneMgmt');
        $zoneFW = resolve('cfZoneFW');
        foreach ($this->zones as $zone) {
            $zone = idn_to_ascii(strtolower(trim($zone)), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $zoneID = $zoneMgmt->getZoneID($zone);
            if (empty($zoneID)) {
                fputcsv($fop, [
                    $zone,
                    $this->ruleDescription,
                    "Failed to check this zone's data on Cloudflare"
                ]);
                continue;
            }
            $rules = $zoneFW->getFWRuleForZone($zoneID, [
                'description' => $this->ruleDescription
            ]);
            if (!$rules) {
                fputcsv($fop, [
                    $zone,
                    $this->ruleDescription,
                    'There is no firewall rule matching the given description under this zone'
                ]);
            } else if (count($rules) == 1) {
                fputcsv($fop, [
                    $zone,
                    $this->ruleDescription,
                    'Found 01 firewall rule matching the given description under this zone'
                ]);
            } else if (count($rules) > 1) {
                fputcsv($fop, [
                    $zone,
                    $this->ruleDescription,
                    'Found some firewall rules matching the given description under this zone'
                ]);
            }
        }
        fclose($fop);
        $report = Report::create([
            'name'     => 'Verify Existence of Cloudflare firewall rule for multiple zones ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id' => $this->user->id,
            'disk'     => $outputFileLocation['disk'],
            'path'     => $outputFileLocation['path'],
            'mime'     => 'text/csv'
        ]);
        VerifyExistenceCFFWRuleCompleted::dispatch($this->user);
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
