<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\FetchCFDNSTargetsForHostnames;
use App\Jobs\FetchDNSHostnameRecordsForZones;
use App\Http\Components\RequestByUserThrottling;

class CFDNSController extends Controller
{
    use RequestByUserThrottling;

    /**
     * Get DNS hostname entries for zones
     *
     * @param \Illuminate\Http\Request
     */
    public function getDNSRecords(Request $request)
    {
        $request->validate([
            'zones' => 'required'
        ]);
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            $zones = array_merge(
                [],
                array_unique(
                    array_map(
                        function ($zone) {
                            return strtolower(trim($zone));
                        },
                        explode(',', $request->zones)
                    )
                )
            );
            $chunks = collect($zones)->chunk(config('mstools.chunk_size.small'));
            $onlyProd = $request->onlyProd ?? null;
            $onlyProxied = $request->onlyProxied ?? null;
            foreach ($chunks as $chunk) {
                FetchDNSHostnameRecordsForZones::dispatch($chunk, $request->user(), $onlyProd, $onlyProxied);
            }
            flashing('MSTool is processing the request')->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 10 seconds before sending another one')->flash();
        }
        return back();
    }

    /**
     * Get Cloudflare DNS targets for hostnames
     *
     * @param \Illuminate\Http\Request
     */
    public function getCFDNSTargets(Request $request)
    {
        $request->validate([
            'hostnames' => 'required'
        ]);
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            $hostnames = array_merge(
                [],
                array_unique(
                    array_map(
                        function ($hostname) {
                            return strtolower(trim($hostname));
                        },
                        explode(',', $request->hostnames)
                    )
                )
            );
            $chunks = collect($hostnames)->chunk(config('mstools.chunk_size.small'));
            foreach ($chunks as $chunk) {
                FetchCFDNSTargetsForHostnames::dispatch($chunk, $request->user(), $chunks->count());
            }
            flashing('MSTool is processing the request')->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 10 seconds before sending another one')->flash();
        }
        return back();
    }
}
