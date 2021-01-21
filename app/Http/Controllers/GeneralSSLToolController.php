<?php

namespace App\Http\Controllers;

use Exception;
use App\Rules\SslKeyMatch;
use App\Rules\SslCertValid;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Jobs\VerifyDomainSSLData;
use App\Jobs\VerifyCFZoneCustomSSL;
use Illuminate\Support\Facades\Gate;
use Spatie\SslCertificate\SslCertificate;
use App\Jobs\UploadCustomCertificateToCloudflare;

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
        VerifyDomainSSLData::dispatch($domains, auth()->user());
        flashing('MSTool is processing the request')
            ->flash();
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
        $zones = explode(',', $request->zones);
        VerifyCFZoneCustomSSL::dispatch($zones, auth()->user());
        flashing('MSTool is processing the request')
            ->flash();
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
        $zones = $this->getZonesForCertUpload($request);
        if (empty($zones)) {
            flashing('No zones have been specified or identified yet')
                ->error()
                ->flash();
        } else {
            UploadCustomCertificateToCloudflare::dispatch($zones, $request->cert, $request->privateKey, auth()->user());
            flashing('MSTool is processing the request')
                ->flash();
            return back();
        }
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
            $ssl = SslCertificate::createFromString($request->cert);
            $domains = $ssl->getAdditionalDomains();
            $zones = [];
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
                $zones = array_merge([], array_unique($zones));
            }
        } else {
            $zones = array_unique(explode(',', $request->zones));
        }
        return $zones;
    }
}
