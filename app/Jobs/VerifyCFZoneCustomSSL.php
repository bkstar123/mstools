<?php
/**
 * VerifyCFZoneCustomSSL job
 *
 * @author: tuanha
 * @last-mod: 23-Jan-2021
 */
namespace App\Jobs;

use Exception;
use App\Events\JobFailing;
use App\Exports\ExcelExport;
use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\VerifyCFZoneCustomSSLCompleted;

class VerifyCFZoneCustomSSL implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $data = [];
        $zoneMgmt = resolve('zoneMgmt');
        $customSSL = resolve('customSSL');
        foreach ($this->zones as $zone) {
            $zone = idn_to_ascii(trim($zone), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $zoneID = $zoneMgmt->getZoneID($zone);
            if ($zoneID === null) {
                array_push($data, [
                    'Zone' => $zone,
                    'Found on Cloudflare' => 'false',
                    'Issuer' => '',
                    'SSL mode' => '',
                    'SSL uploaded on' => '',
                    'SSL modified on' => '',
                    'Expired_at' => '',
                    'Hosts' => '',
                    'Note' => ''
                ]);
                continue;
            } elseif ($zoneID === false) {
                array_push($data, [
                    'Zone' => $zone,
                    'Found on Cloudflare' => 'Unknown',
                    'Issuer' => '',
                    'SSL mode' => '',
                    'SSL uploaded on' => '',
                    'SSL modified on' => '',
                    'Expired_at' => '',
                    'Hosts' => '',
                    'Note' => 'Something is unusual, please manually double check in the Cloudflare portal'
                ]);
                continue;
            }
            $currentCertID = $customSSL->getCurrentCustomCertID($zoneID);
            $sslMode = $zoneMgmt->getZoneSSLMode($zoneID);
            if ($currentCertID === false) {
                array_push($data, [
                    'Zone' => $zone,
                    'Found on Cloudflare' => 'true',
                    'Issuer' => '',
                    'SSL mode' => !empty($sslMode) ? $sslMode : null,
                    'SSL uploaded on' => '',
                    'SSL modified on' => '',
                    'Expired_at' => '',
                    'Hosts' => '',
                    'Note' => 'Something is unusual, please manually double check in the Cloudflare portal'
                ]);
                continue;
            } elseif ($currentCertID === null) {
                array_push($data, [
                    'Zone' => $zone,
                    'Found on Cloudflare' => 'true',
                    'Issuer' => '',
                    'SSL mode' => !empty($sslMode) ? $sslMode : null,
                    'SSL uploaded on' => '',
                    'SSL modified on' => '',
                    'Expired_at' => '',
                    'Hosts' => '',
                    'Note' => ''
                ]);
            } else {
                $res = $customSSL->fetchCertData($zoneID, $currentCertID);
                if (!$res) {
                    array_push($data, [
                        'Zone' => $zone,
                        'Found on Cloudflare' => 'true',
                        'Issuer' => '',
                        'SSL mode' => !empty($sslMode) ? $sslMode : null,
                        'SSL uploaded on' => '',
                        'SSL modified on' => '',
                        'Expired_at' => '',
                        'Hosts' => '',
                        'Note' => 'Something is unusual, please manually double check in the Cloudflare portal'
                    ]);
                    continue;
                } else {
                    array_push($data, [
                        'Zone' => $zone,
                        'Found on Cloudflare' => 'true',
                        'Issuer' => $res['issuer'],
                        'SSL mode' => !empty($sslMode) ? $sslMode : null,
                        'SSL uploaded on' => $res['uploaded_on'],
                        'SSL modified on' => $res['modified_on'],
                        'Expired_at' => $res['expires_on'],
                        'Hosts' => $res['hosts'],
                        'Note' => ''
                    ]);
                }
            }
        }
        $headings = [
            'Zone',
            'Found on Cloudflare',
            'Issuer',
            'SSL mode',
            'SSL uploaded on',
            'SSL modified on',
            'Expired_at',
            'Hosts',
            'Note'
        ];
        VerifyCFZoneCustomSSLCompleted::dispatch(Excel::raw(new ExcelExport($data, $headings), 'Xlsx'), $this->zones, $this->user);
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
