<?php
/**
 * UploadCustomCertificateToCloudflare Job
 *
 * @author: tuanha
 * @last-mod:16-Jan-2021
 */
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\UploadCustomCertificateToCloudflareCompleted;

class UploadCustomCertificateToCloudflare implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    protected $zones;

    /**
     * @var string
     */
    protected $cert;

    /**
     * @var string
     */
    protected $key;

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
     * @return void
     */
    public function __construct($zones, $cert, $key, $user)
    {
        $this->zones = $zones;
        $this->cert = $cert;
        $this->key = $key;
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
            'Zone', 'isCompleted', 'isSSLReplacement', 'Comment'
        ]);
        $zoneMgmt = resolve('zoneMgmt');
        $customSSL = resolve('customSSL');
        foreach ($this->zones as $zone) {
            $zone = idn_to_ascii(trim($zone), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $zoneID = $zoneMgmt->getZoneID($zone);
            if (empty($zoneID)) {
                fputcsv($fh, [$zone, 'No', 'Unknown', "Failed to check this zone's data on Cloudflare" ]);
                continue;
            }
            $currentCertID = $customSSL->getCurrentCustomCertID($zoneID);
            if ($currentCertID === false) {
                fputcsv($fh, [$zone, 'No', 'Unknown', "Failed to check the zone's custom SSL settings, or the configuration is unusal" ]);
                continue;
            } elseif ($currentCertID === null) {
                if (!$customSSL->uploadNewCustomCert($zoneID, $this->cert, $this->key)) {
                    fputcsv($fh, [$zone, 'No', 'No', "No existing certificate was found, the new certificate failed to be installed" ]);
                    continue;
                } else {
                    fputcsv($fh, [$zone, 'Yes', 'No', "No existing certificate was found, the new certificate has been successfully installed" ]);
                }
            } else {
                if (!$customSSL->updateCustomCert($zoneID, $currentCertID, $this->cert, $this->key)) {
                    fputcsv($fh, [$zone, 'No', 'Yes', "An existing certificate was found, the SSL replacement failed" ]);
                    continue;
                } else {
                    fputcsv($fh, [$zone, 'Yes', 'Yes', "An existing certificate was found, the SSL replacement has been succeeded" ]);
                }
            }
        }
        rewind($fh);
        UploadCustomCertificateToCloudflareCompleted::dispatch(stream_get_contents($fh), $this->zones, $this->user);
        fclose($fh);
    }
}
