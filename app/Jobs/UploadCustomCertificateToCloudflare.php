<?php
/**
 * UploadCustomCertificateToCloudflare Job
 *
 * @author: tuanha
 * @last-mod:16-Jan-2021
 */
namespace App\Jobs;

use App\Exports\ExcelExport;
use Illuminate\Bus\Queueable;
use App\Jobs\VerifyCFZoneCustomSSL;
use Maatwebsite\Excel\Facades\Excel;
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
        $data = [];
        $zoneMgmt = resolve('zoneMgmt');
        $customSSL = resolve('customSSL');
        foreach ($this->zones as $zone) {
            $zone = idn_to_ascii(trim($zone), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $zoneID = $zoneMgmt->getZoneID($zone);
            if (empty($zoneID)) {
                array_push($data, [
                    'Zone' => $zone,
                    'isCompleted' => 'No',
                    'isSSLReplacement' => 'Unknown',
                    'Comment' => "Failed to check this zone's data on Cloudflare"
                ]);
                continue;
            }
            $currentCertID = $customSSL->getCurrentCustomCertID($zoneID);
            if ($currentCertID === false) {
                array_push($data, [
                    'Zone' => $zone,
                    'isCompleted' => 'No',
                    'isSSLReplacement' => 'Unknown',
                    'Comment' => "Failed to check the zone's custom SSL settings, or the configuration is unusal"
                ]);
                continue;
            } elseif ($currentCertID === null) {
                if (!$customSSL->uploadNewCustomCert($zoneID, $this->cert, $this->key)) {
                    array_push($data, [
                        'Zone' => $zone,
                        'isCompleted' => 'No',
                        'isSSLReplacement' => 'No',
                        'Comment' => "No existing certificate was found, the new certificate failed to be installed"
                    ]);
                    continue;
                } else {
                    array_push($data, [
                        'Zone' => $zone,
                        'isCompleted' => 'Yes',
                        'isSSLReplacement' => 'No',
                        'Comment' => "No existing certificate was found, the new certificate has been successfully installed"
                    ]);
                }
            } else {
                if (!$customSSL->updateCustomCert($zoneID, $currentCertID, $this->cert, $this->key)) {
                    array_push($data, [
                        'Zone' => $zone,
                        'isCompleted' => 'No',
                        'isSSLReplacement' => 'Yes',
                        'Comment' => "An existing certificate was found, the SSL replacement failed"
                    ]);
                    continue;
                } else {
                    array_push($data, [
                        'Zone' => $zone,
                        'isCompleted' => 'Yes',
                        'isSSLReplacement' => 'Yes',
                        'Comment' => "An existing certificate was found, the SSL replacement has been succeeded"
                    ]);
                }
            }
        }
        $headings = ['Zone', 'isCompleted', 'isSSLReplacement', 'Comment'];
        UploadCustomCertificateToCloudflareCompleted::dispatch(Excel::raw(new ExcelExport($data, $headings), 'Xlsx'), $this->zones, $this->user);
        VerifyCFZoneCustomSSL::dispatch($this->zones, $this->user);
    }
}
