<?php
/**
 * Get all full zones on Cloudflare
 *
 * @author: tuanha
 * @date: 11-Dec-2022
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScanForFullZones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudflare:scanForFullZones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan for all Cloudflare full zones';

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
        $zoneMgmt = resolve('zoneMgmt');
        $fullZones = [];
        $page = 1;
        do {
            $zones = $zoneMgmt->getPaginatedZones($page, 1000);
            if (empty($zones)) {
                break;
            }
            $data = array_filter($zones, function ($zone) {
                return isset($zone['type']) && $zone['type'] == 'full';
            });
            if (!empty($data)) {
                $data = array_merge([], array_map(function ($zone) {
                    return $zone['name'];
                }, $data));
            }
            $fullZones = array_merge($fullZones, $data);
            ++$page;
        } while (!empty($zones));
        file_put_contents(storage_path('app/cloudflare_full_zones.txt'), json_encode($fullZones));
    }
}
