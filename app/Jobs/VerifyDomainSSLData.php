<?php
/**
 * VerifyDomainSSLData Job
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
use Spatie\SslCertificate\SslCertificate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\VerifyDomainSSLDataCompleted;
use App\Http\Components\GenerateCustomUniqueString;

class VerifyDomainSSLData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GenerateCustomUniqueString;

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
        $outputFileLocation = [
            'disk' => config('mstools.report.disk'),
            'path' => config('mstools.report.directory').DIRECTORY_SEPARATOR.$this->generateUniqueString().DIRECTORY_SEPARATOR.$this->generateUniqueString('.csv')
        ];
        Storage::disk($outputFileLocation['disk'])->makeDirectory(dirname($outputFileLocation['path']));
        $fop = fopen(Storage::disk($outputFileLocation['disk'])->path($outputFileLocation['path']), 'w');
        fputcsv($fop, [
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
        ]);
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
                fputcsv($fop, [
                    $domain,
                    $cert->getIssuer(),
                    $cert->validFromDate(),
                    $cert->expirationDate(),
                    $cert->getDomain(),
                    $cert->getFingerprint(),
                    $cert->daysUntilExpirationDate(),
                    json_encode($IPs),
                    json_encode($Aliases),
                    json_encode($cert->getAdditionalDomains()),
                ]);
            } catch (Exception $e) {
                fputcsv($fop, [$domain,'','','','','','',json_encode($IPs),json_encode($Aliases),'']);
            }
        }
        fclose($fop);
        $report = Report::create([
            'name'     => 'Verify SSL certificate for domains ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id' => $this->user->id,
            'disk'     => $outputFileLocation['disk'],
            'path'     => $outputFileLocation['path'],
            'mime'     => 'text/csv'
        ]);
        VerifyDomainSSLDataCompleted::dispatch($report, $this->domains, $this->user);
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
