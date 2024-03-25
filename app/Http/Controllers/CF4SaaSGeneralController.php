<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ExportCF4SaaSHostnames;
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
            'saasHostnames' => 'required',
        ]);
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            $hostnames = explode(",", $request->saasHostnames);
            $hostnames = array_map(function ($hostname) {
                return idn_to_ascii(trim($hostname), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            }, $hostnames);
            return getOriginServerOfCF4SaasHostname($hostnames);
        }
    }

    /**
     * Export details of the given CF4SaaS hostnames
     *
     * @param Illuminate\Http\Request
     * @return Illuminate\Http\Response
     */
    public function exportCF4SaaSHostnames(Request $request)
    {
        $request->validate([
            'saasHostnames' => 'required',
        ]);
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            if ($request->saasHostnames == "*") {
                $hostnames = []; // if given "*", then export full list of CF4SaaS hostnames
            } else {
                $hostnames = explode(",", $request->saasHostnames);
                $hostnames = array_map(function ($hostname) {
                    return idn_to_ascii(trim($hostname), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
                }, $hostnames);
            }
            ExportCF4SaaSHostnames::dispatch(auth()->user(), $hostnames);
        }
    }
}
