<?php
/**
 * UploadCustomCertificateToCloudflare Job
 *
 * @author: tuanha
 * @last-mod:16-Jan-2021
 */
namespace App\Jobs;

use Exception;
use App\Report;
use Carbon\Carbon;
use App\Events\JobFailing;
use Illuminate\Bus\Queueable;
use App\Jobs\VerifyCFZoneCustomSSL;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\SslCertificate\SslCertificate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Components\GenerateCustomUniqueString;
use App\Events\UploadCustomCertificateToCloudflareCompleted;

class UploadCustomCertificateToCloudflare implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GenerateCustomUniqueString;

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
     * @var bool
     */
    protected $useDeepValidation;

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
    public function __construct($zones, $cert, $key, $user, $useDeepValidation)
    {
        $this->zones = $zones;
        $this->cert = $cert;
        $this->key = $key;
        $this->user = $user;
        $this->useDeepValidation = $useDeepValidation;
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
            'isCompleted',
            'isSSLReplacement',
            'Comment'
        ]);
        $zoneMgmt = resolve('zoneMgmt');
        $customSSL = resolve('customSSL');
        foreach ($this->zones as $zone) {
            $zone = idn_to_ascii(strtolower(trim($zone)), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $zoneID = $zoneMgmt->getZoneID($zone);
            if (empty($zoneID)) {
                fputcsv($fop, [
                    $zone,
                    'No',
                    'Unknown',
                    "Failed to check this zone's data on Cloudflare"
                ]);
                continue;
            }
            $currentCertID = $customSSL->getCurrentCustomCertID($zoneID);
            if ($currentCertID === false) {
                fputcsv($fop, [
                    $zone,
                    'No',
                    'Unknown',
                    "Failed to check the zone's custom SSL settings, or the configuration is unusal"
                ]);
                continue;
            } elseif ($currentCertID === null) {
                if (!$customSSL->uploadNewCustomCert($zoneID, $this->cert, $this->key)) {
                    fputcsv($fop, [
                        $zone,
                        'No',
                        'No',
                        "No existing certificate was found, the new certificate failed to be installed"
                    ]);
                    continue;
                } else {
                    fputcsv($fop, [
                        $zone,
                        'Yes',
                        'No',
                        "No existing certificate was found, the new certificate has been successfully installed"
                    ]);
                }
            } else {
                $validate = $this->preReplaceValidate($zoneID, $currentCertID, $this->cert, $customSSL, $this->useDeepValidation);
                if ($validate['isOK']) {
                    if (!$customSSL->updateCustomCert($zoneID, $currentCertID, $this->cert, $this->key)) {
                        fputcsv($fop, [
                            $zone,
                            'No',
                            'Yes',
                            "An existing certificate was found, the SSL replacement failed"
                        ]);
                        continue;
                    } else {
                        fputcsv($fop, [
                            $zone,
                            'Yes',
                            'Yes',
                            "An existing certificate was found, the SSL replacement has been succeeded"
                        ]);
                    }
                } else {
                    fputcsv($fop, [
                        $zone,
                        'No',
                        'Yes',
                        empty($validate['diff']) ?
                            "Cannot validate the new certificate's domains with those of the existing certificate" :
                            "Not being proceeded yet. The existing certificate differs from the new one on the following domains: " .
                            json_encode($validate['diff']) .
                            ". Please contact the super admin if you are sure to replace SSL for this zone"
                    ]);
                    continue;
                }
            }
        }
        fclose($fop);
        $report = Report::create([
            'name'     => 'Result of SSL uploading to Cloudflares ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id' => $this->user->id,
            'disk'     => $outputFileLocation['disk'],
            'path'     => $outputFileLocation['path'],
            'mime'     => 'text/csv'
        ]);
        UploadCustomCertificateToCloudflareCompleted::dispatch($this->user);
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
     * @param bool $useDeepValidation
     * @return array
     */
    protected function preReplaceValidate($zoneID, $certID, $cert, $customSSL, $useDeepValidation = false)
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
        $existingCertDomains = (array) json_decode($data['hosts'], true);
        $normalizedExistingCertDomains = array_map(function ($hostname) {
            return strtolower(idn_to_ascii(trim($hostname), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46));
        }, $existingCertDomains);
        $newCertDomains = $this->getCertificateDomains($cert);
        $normalizedNewCertDomains = array_map(function ($hostname) {
            return strtolower(idn_to_ascii(trim($hostname), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46));
        }, $newCertDomains);
        $diffHostnames = array_merge([], array_diff($normalizedExistingCertDomains, $normalizedNewCertDomains));
        if (count($diffHostnames) > 0) {
            $diffHostnames = array_filter($diffHostnames, function ($hostname) use ($normalizedNewCertDomains) {
                return !in_array(toWildcardHostname($hostname), $normalizedNewCertDomains);
            });
            $diffHostnames = array_merge([], $diffHostnames);
            if (count($diffHostnames) > 0 && $useDeepValidation) {
                $zoneMgmt = resolve('zoneMgmt');
                $zoneCurrentProductionHostnames = $zoneMgmt->getZoneSubDomains($zoneID, null, true, true);
                $diffHostnames = array_filter($diffHostnames, function ($hostname) use ($zoneCurrentProductionHostnames) {
                    return in_array($hostname, $zoneCurrentProductionHostnames);
                });
                $diffHostnames = array_merge([], $diffHostnames);
            }
        }
        return [
            'isOK' => empty($diffHostnames),
            'diff' => $diffHostnames
        ];
    }
}
