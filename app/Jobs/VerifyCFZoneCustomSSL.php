<?php
/**
 * VerifyCFZoneCustomSSL job
 *
 * @author: tuanha
 * @last-mod: 10-Jan-2021
 */
namespace App\Jobs;

use Illuminate\Bus\Queueable;
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
        $fh = fopen('php://temp', 'w');
        fputcsv($fh, [
            'Zone', 'Found on Cloudflare', 'Issuer', 'SSL mode', 'SSL uploaded on', 'SSL modified on', 'Expired_at', 'Hosts', 'Note'
        ]);
        $zoneMgmt = resolve('zoneMgmt');
        $customSSL = resolve('customSSL');
        foreach ($this->zones as $zone) {
            $zone = idn_to_ascii(trim($zone), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $zoneID = $zoneMgmt->getZoneID($zone);
            if ($zoneID === null) {
                fputcsv($fh, [$zone, 'false', '', '', '', '', '', '', '']);
                continue;
            } elseif ($zoneID === false) {
                fputcsv($fh, [$zone, 'Unknown', '', '', '', '', '', '', 'Something is unusual, please manually double check in the Cloudflare portal']);
                continue;
            }
            $currentCertID = $customSSL->getCurrentCustomCertID($zoneID);
            if ($currentCertID === false) {
                fputcsv($fh, [$zone, 'true', '', '', '', '', '', '', 'Something is unusual, please manually double check in the Cloudflare portal']);
                continue;
            } elseif ($currentCertID === null) {
                fputcsv($fh, [$zone, 'true', '', '', '', '', '', '', '']);
            } else {
                $data = $customSSL->fetchCertData($zoneID, $currentCertID);
                if (!$data) {
                    fputcsv($fh, [$zone, 'true', '', '', '', '', '', '', 'Something is unusual, please manually double check in the Cloudflare portal']);
                    continue;
                } else {
                    fputcsv($fh, [
                        $zone,
                        'true',
                        $data['issuer'],
                        $data['tls_mode'],
                        $data['uploaded_on'],
                        $data['modified_on'],
                        $data['expires_on'],
                        $data['hosts']
                    ]);
                }
            }
        }
        rewind($fh);
        VerifyCFZoneCustomSSLCompleted::dispatch(stream_get_contents($fh), $this->zones, $this->user);
        fclose($fh);
    }
}
