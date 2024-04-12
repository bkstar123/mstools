<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScanCF4SaaSHostnames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudflare:scanCF4SaaSHostnames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan for all Cloudflare-for-SaaS hostnames and cache them on the server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cfZones = getAllCFZonesFromCache();
        if (empty($cfZones)) {
            return;
        }
        $saasZones = array_merge([], array_filter($cfZones, function ($zone) {
            return stristr($zone, config('mstools.tracking.saas_fallback_url_suffix'));
        }));
        $saasHostnames = [];
        $zoneMgmt = resolve('zoneMgmt');
        foreach ($saasZones as $zone) {
            $zoneID = $zoneMgmt->getZoneID($zone);
            if (empty($zoneID)) {
                continue;
            }
            $data = $zoneMgmt->getCF4SaaSCustomHostnames($zoneID, true);
            if ($data) {
                $saasHostnames = array_merge($saasHostnames, $data);
            }
        }
        file_put_contents(storage_path('app/cloudflare_saas_hostnames.txt'), json_encode($saasHostnames));
    }
}
