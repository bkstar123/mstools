<?php
/**
 * GeneralSSLToolController
 *
 * @author: tuanha
 * @last-mod: 10-Feb-2021
 */
namespace App\Http\Controllers;

use Exception;
use App\Rules\SslKeyMatch;
use App\Rules\SslCertValid;
use Illuminate\Http\Request;
use App\Jobs\VerifyDomainSSLData;
use App\Jobs\VerifyCFZoneCustomSSL;
use Spatie\SslCertificate\SslCertificate;
use App\Http\Components\RequestByUserThrottling;
use App\Jobs\UploadCustomCertificateToCloudflare;

class GeneralSSLToolController extends Controller
{
    use RequestByUserThrottling;

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
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            $domains = explode(',', $request->domains);
            VerifyDomainSSLData::dispatch($domains, auth()->user());
            flashing('MSTool is processing the request')->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 10 seconds before sending another one')->flash();
        }
        return back();
    }

    /**
     * Verify custom SSL setting for Cloudflare zone
     *
     * @param \Illuminate\Http\Request $request
     */
    public function verifyCFZoneCustomSSL(Request $request)
    {
        $request->validate([
            'zones' => 'required'
        ]);
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            $zones = explode(',', $request->zones);
            VerifyCFZoneCustomSSL::dispatch($zones, auth()->user());
            flashing('MSTool is processing the request')->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 10 seconds before sending another one')->flash();
        }
        return back();
    }

    /**
     * Decode a certificate
     *
     * @param \Illuminate\Http\Request $request
     */
    public function verifyCertData(Request $request)
    {
        $request->validate([
            'cert' => 'required'
        ]);
        try {
            $ssl = SslCertificate::createFromString($request->cert);
            return view('cms.checkcertdata', compact('ssl'));
        } catch (Exception $e) {
            flashing('Cannot parse the content of the certificate')
            ->error()
            ->flash();
            return back();
        }
    }

    /**
     * Upload certificate to Cloudflare
     *
     * @param \Illuminate\Http\Request $request
     */
    public function uploadCertCFZone(Request $request)
    {
        $request->validate([
            'cert' => ['required', new SslCertValid],
            'privateKey' => ['required', new SslKeyMatch($request->cert)]
        ]);
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            $zones = $this->getZonesForCertUpload($request);
            if (empty($zones)) {
                flashing('No zones have been specified or identified yet')
                ->error()
                ->flash();
            } else {
                UploadCustomCertificateToCloudflare::dispatch($zones, $request->cert, $request->privateKey, auth()->user());
                flashing('MSTool is processing the request')
                ->flash();
            }
        } else {
            flashing('MSTool is busy processing your first request, please wait for 10 seconds before sending another one')->flash();
        }
        return back();
    }

    /**
     * Matching private key / certificate
     *
     * @param \Illuminate\Http\Request $request
     */
    public function keyCertMatching(Request $request)
    {
        $request->validate([
            'cert' => ['required', new SslCertValid],
            'privateKey' => ['required', new SslKeyMatch($request->cert)]
        ]);
        flashing('The private key matches with the certificate')
            ->success()
            ->flash();
        return back();
    }

    /**
     * Get zones for certificate upload
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function getZonesForCertUpload(Request $request)
    {
        if (empty($request->zones)) {
            $zones = [];
            try {
                $ssl = SslCertificate::createFromString($request->cert);
            } catch (Exception $e) {
                return $zones;
            }
            $domains = $ssl->getAdditionalDomains();
            if (count($domains) > 0) {
                $TLDs = explode(',', file_get_contents(asset('/sources/tlds.txt')));
                foreach ($domains as $domain) {
                    $domainParts = explode('.', trim($domain));
                    $i = count($domainParts) - 1;
                    $zone = $domainParts[$i];
                    while ($i >= 0 && in_array($zone, $TLDs)) {
                        --$i;
                        $zone = $domainParts[$i].'.'.$zone;
                    }
                    $zones[] = $zone;
                }
                $zones = array_map(function ($zone) {
                    return strtolower($zone);
                }, $zones);
                $zones = array_merge([], array_unique($zones));
            }
            return $zones;
        } else {
            $zones = array_map(function ($zone) {
                return strtolower(trim($zone));
            }, explode(',', $request->zones));
            return array_unique($zones);
        }
    }
}
