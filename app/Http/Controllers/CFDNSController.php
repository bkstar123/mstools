<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\FetchDNSHostnameRecordsForZones;
use App\Http\Components\RequestByUserThrottling;

class CFDNSController extends Controller
{
    use RequestByUserThrottling;

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
            $onlyProd = $request->onlyProd ?? null;
            $onlyProxied = $request->onlyProxied ?? null;
            FetchDNSHostnameRecordsForZones::dispatch($zones, $request->user(), $onlyProd, $onlyProxied);
            flashing('MSTool is processing the request')->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 10 seconds before sending another one')->flash();
        }
        return back();
    }
}
