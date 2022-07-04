<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\CheckAAndCnameDnsRecord;
use App\Http\Components\RequestByUserThrottling;

class DnsController extends Controller
{
    use RequestByUserThrottling;

    /**
     * Check DNS A & CNAME records for domains
     *
     * @param \Illuminate\Http\Request $request
     */
    public function checkDns(Request $request)
    {
        $request->validate([
            'domains' => 'required'
        ]);
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            $domains = explode(',', $request->domains);
            CheckAAndCnameDnsRecord::dispatch($domains, auth()->user());
            flashing('MSTool is processing the request')->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 10 seconds before sending another one')->warning()->flash();
        }
        return back();
    }
}
