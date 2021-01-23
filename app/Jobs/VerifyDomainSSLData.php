<?php
/**
 * VerifyDomainSSLData Job
 *
 * @author: tuanha
 * @last-mod: 23-Jan-2021
 */
namespace App\Jobs;

use Exception;
use App\Exports\ExcelExport;
use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\SslCertificate\SslCertificate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\VerifyDomainSSLDataCompleted;

class VerifyDomainSSLData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    protected $domains;

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
     * @param $domains array
     * @param $user \Bkstar123\BksCMS\AdminPanel\Admin
     * @return void
     */
    public function __construct($domains, $user)
    {
        $this->domains = $domains;
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
        foreach ($this->domains as $domain) {
            $domain = idn_to_ascii(trim($domain), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $IPs = [];
            $Aliases = [];
            try {
                $a_records = dns_get_record($domain, DNS_A);
            } catch (Exception $e) {
                $a_records = [];
            }
            try {
                $cname_records = dns_get_record($domain, DNS_CNAME);
            } catch (Exception $e) {
                $cname_records = [];
            }
            if (!empty($a_records)) {
                foreach ($a_records as $record) {
                    array_push($IPs, $record['ip']);
                }
            }
            if (!empty($cname_records)) {
                foreach ($cname_records as $record) {
                    array_push($Aliases, $record['target']);
                }
            }
            try {
                $cert = SslCertificate::createForHostName($domain);
                array_push($data, [
                    'URL' => $domain,
                    'Issuer' => $cert->getIssuer(),
                    'Valid_from' => $cert->validFromDate(),
                    'Expired_at' => $cert->expirationDate(),
                    'CN' => $cert->getDomain(),
                    'Fingerprint' => $cert->getFingerprint(),
                    'Remaining_days' => $cert->daysUntilExpirationDate(),
                    'A' => json_encode($IPs),
                    'CNAME' => json_encode($Aliases),
                    'SAN' => json_encode($cert->getAdditionalDomains()),
                ]);
            } catch (Exception $e) {
                array_push($data, [
                    'URL' => $domain,
                    'Issuer' => '',
                    'Valid_from' => '',
                    'Expired_at' => '',
                    'CN' => '',
                    'Fingerprint' => '',
                    'Remaining_days' => '',
                    'A' => json_encode($IPs),
                    'CNAME' => json_encode($Aliases),
                    'SAN' => '',
                ]);
            }
        }
        $headings = [
            'URL',
            'Issuer',
            'Valid_from',
            'Expired_at',
            'CN',
            'Fingerprint',
            'Remaining_days',
            'A',
            'CNAME',
            'SAN'
        ];
        VerifyDomainSSLDataCompleted::dispatch(Excel::raw(new ExcelExport($data, $headings), 'Xlsx'), $this->domains, $this->user);
    }
}
