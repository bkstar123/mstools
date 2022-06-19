<?php
/**
 * UploadCustomCertificateToCloudflare Job
 *
 * @author: tuanha
 * @last-mod:16-Jan-2021
 */
namespace App\Jobs;

use Exception;
use App\Events\JobFailing;
use App\Exports\ExcelExport;
use Illuminate\Bus\Queueable;
use App\Jobs\VerifyCFZoneCustomSSL;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\SslCertificate\SslCertificate;
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
                $validate = $this->preReplaceValidate($zoneID, $currentCertID, $this->cert, $customSSL);
                if ($validate['isOK']) {
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
                } else {
                    array_push($data, [
                        'Zone' => $zone,
                        'isCompleted' => 'No',
                        'isSSLReplacement' => 'Yes',
                        'Comment' => empty($validate['diff']) ?
                            "Cannot validate the new certificate's domains with those of the existing certificate" :
                            "Not being proceeded yet. The existing certificate differs from the new one on the following domains: " .
                            json_encode($validate['diff']) .
                            ". Please contact the super admin if you are sure to replace SSL for this zone"
                    ]);
                    continue;
                }
            }
        }
        $headings = ['Zone', 'isCompleted', 'isSSLReplacement', 'Comment'];
        UploadCustomCertificateToCloudflareCompleted::dispatch(Excel::raw(new ExcelExport($data, $headings), 'Xlsx'), $this->zones, $this->user);
        VerifyCFZoneCustomSSL::dispatch($this->zones, $this->user);
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

    /**
     * @param string $cert
     * @return array
     */
    protected function getCertificateDomains($cert)
    {
        try {
            return SslCertificate::createFromString($cert)->getAdditionalDomains();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @param string $zoneID
     * @param string $certID
     * @param string $cert
     * @param \Bkstar123\CFBuddy\Services\CustomSSL $customSSL
     * @return array
     */
    protected function preReplaceValidate($zoneID, $certID, $cert, $customSSL)
    {
        if ($this->user->can('certificate.pre.replacement.validation.bypass')) {
            return [
                'isOK' => true,
                'diff' => []
            ];
        }
        $data = $customSSL->fetchCertData($zoneID, $certID);
        if (!$data) {
            return [
                'isOK' => false,
                'diff' => []
            ];
        }
        $existingCertDomains = json_decode($data['hosts'], true);
        $newCertDomains = $this->getCertificateDomains($cert);
        $diff = array_merge([], array_diff($existingCertDomains, $newCertDomains));
        return [
            'isOK' => empty($diff),
            'diff' => $diff
        ];
    }
}
