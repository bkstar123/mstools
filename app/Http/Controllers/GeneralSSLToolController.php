<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\VerifyDomainSSLData;
use App\Jobs\VerifyCFZoneCustomSSL;

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
}
