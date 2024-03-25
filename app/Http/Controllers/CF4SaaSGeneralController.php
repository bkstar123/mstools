<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Components\RequestByUserThrottling;

class CF4SaaSGeneralController extends Controller
{
    use RequestByUserThrottling;

    /**
     * Get custom origin server of a given CF-for-SaaS hostname
     *
     * @param Illuminate\Http\Request
     * @return Illuminate\Http\Response
     */
    public function getCustomOriginServer(Request $request)
    {
        $request->validate([
            'saasHostname' => 'required',
        ]);
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            return array_first(getOriginServerOfCF4SaasHostname($request->saasHostname));
        }
    }
}
