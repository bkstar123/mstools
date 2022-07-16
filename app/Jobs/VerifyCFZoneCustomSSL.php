<?php
/**
 * VerifyCFZoneCustomSSL job
 *
 * @author: tuanha
 * @last-mod: 23-Jan-2021
 */
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
use App\Events\VerifyCFZoneCustomSSLCompleted;
use App\Http\Components\GenerateCustomUniqueString;

class VerifyCFZoneCustomSSL implements ShouldQueue
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
     * @return void
     */
    public function __construct($zones, $user)
    {
        $this->zones = $zones;
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
            'Found on Cloudflare',
            'Issuer',
            'SSL mode',
            'SSL uploaded on',
            'SSL modified on',
            'Expired_at',
            'Hosts',
            'Note'
        ]);
        $zoneMgmt = resolve('zoneMgmt');
        $customSSL = resolve('customSSL');
        foreach ($this->zones as $zone) {
            $zone = idn_to_ascii(trim($zone), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $zoneID = $zoneMgmt->getZoneID($zone);
            if ($zoneID === null) {
                fputcsv($fop, [$zone,'false','','','','','','','']);
                continue;
            } elseif ($zoneID === false) {
                fputcsv($fop, [$zone,'Unknown','','','','','','','Something is unusual, please manually double check in the Cloudflare portal']);
                continue;
            }
            $currentCertID = $customSSL->getCurrentCustomCertID($zoneID);
            $sslMode = $zoneMgmt->getZoneSSLMode($zoneID);
            if ($currentCertID === false) {
                fputcsv($fop, [$zone,'true','',!empty($sslMode) ? $sslMode : null,'','','','','Something is unusual, please manually double check in the Cloudflare portal']);
                continue;
            } elseif ($currentCertID === null) {
                fputcsv($fop, [$zone,'true','',!empty($sslMode) ? $sslMode : null,'','','','','']);
            } else {
                $res = $customSSL->fetchCertData($zoneID, $currentCertID);
                if (!$res) {
                    fputcsv($fop, [$zone,'true','',!empty($sslMode) ? $sslMode : null,'','','','','Something is unusual, please manually double check in the Cloudflare portal']);
                    continue;
                } else {
                    fputcsv($fop, [
                        $zone,
                        'true',
                        $res['issuer'],
                        !empty($sslMode) ? $sslMode : null,
                        $res['uploaded_on'],
                        $res['modified_on'],
                        $res['expires_on'],
                        $res['hosts'],
                        ''
                    ]);
                }
            }
        }
        fclose($fop);
        $report = Report::create([
            'name'     => 'Verify custom SSL configuration for Cloudflare zones ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id' => $this->user->id,
            'disk'     => $outputFileLocation['disk'],
            'path'     => $outputFileLocation['path'],
            'mime'     => 'text/csv'
        ]);
        VerifyCFZoneCustomSSLCompleted::dispatch($this->user);
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
