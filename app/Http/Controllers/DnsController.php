<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\CheckAAndCnameDnsRecord;
use App\Http\Components\RequestByUserThrottling;

class DnsController extends Controller
{
    use RequestByUserThrottling;

    /**
     * Query DNS A & CNAME records for domains
     *
     * @param \Illuminate\Http\Request $request
     */
    public function queryDns(Request $request)
    {
        $request->validate([
            'domains' => 'required'
        ]);
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            $domains = array_merge(
                [],
                array_unique(
                    array_map(
                        function ($domain) {
                            return strtolower(trim($domain));
                        },
                        explode(',', $request->domains)
                    )
                )
            );
            $chunks = collect($domains)->chunk(config('mstools.chunk_size.large'));
            foreach ($chunks as $chunk) {
                CheckAAndCnameDnsRecord::dispatch($chunk, auth()->user(), $chunks->count());
            }
            flashing('MSTool is processing the request')->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 10 seconds before sending another one')->warning()->flash();
        }
        return back();
    }
}
