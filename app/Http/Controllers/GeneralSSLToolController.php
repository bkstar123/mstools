<?php

namespace App\Http\Controllers;

use Exception;
use League\Csv\Writer;
use Illuminate\Http\Request;
use App\Jobs\VerifyCFZoneCustomSSL;
use Spatie\SslCertificate\SslCertificate;

class GeneralSSLToolController extends Controller
{
    /**
     * Verify SSL certificate data for domains
     *
     * @param \Illuminate\Http\Request $request
     */
    public function verifyDomainSSLData(Request $request)
    {
        $request->validate([
            'domains' => 'required'
        ]);
        $domains = explode(',', $request->domains);
        $results = function () use ($domains) {
            foreach ($domains as $domain) {
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
                    yield [
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
                    ];
                } catch (Exception $e) {
                    yield [$domain, '', '', '', '', '', '', json_encode($IPs), json_encode($Aliases), ''];
                }
            }
        };
        $data = $results();
        return response()->streamDownload(function () use ($data) {
            $csvWriter = Writer::createFromFileObject(new \SplFileObject('php://output', 'w+'));
            $csvWriter->insertOne(['URL', 'Issuer', 'Valid_from', 'Expired_at', 'CN', 'Fingerprint', 'Remaining_days', 'A', 'CNAME', 'SAN']);
            $csvWriter->insertAll($data);
        }, 'check_ssl.csv', [
            'COntent-Type' => 'text/csv'
        ]);
    }

    /**
     * Verify custom SSL setting for Cloudflare zone
     *
     * @param \Illuminate\Http\Request $request
     */
    public function verifyCustomSSLForCFZones(Request $request)
    {
        $request->validate([
            'zones' => 'required'
        ]);
        $zones = explode(',', $request->zones);
        VerifyCFZoneCustomSSL::dispatch($zones, auth()->user());
        flashing('MSTool will send you the result via email')
            ->flash();
        return back();
    }
}
