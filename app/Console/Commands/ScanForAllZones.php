<?php
/**
 * Get all full zones on Cloudflare
 *
 * @author: tuanha
 * @date: 11-Dec-2022
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScanForAllZones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudflare:scanForAllZones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan for all Cloudflare zones and cache them on the server';

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
        $allZones = [];
        $page = 1;
        do {
            $zones = $zoneMgmt->getPaginatedZones($page, 1000);
            if (empty($zones)) {
                break;
            }
            if (!empty($zones)) {
                $data = array_merge([], array_map(function ($zone) {
                    return idn_to_ascii(strtolower(trim($zone['name'])), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
                }, $zones));
            }
            $allZones = array_merge($allZones, $data);
            ++$page;
        } while (!empty($zones));
        file_put_contents(storage_path('app/cloudflare_all_zones.txt'), json_encode($allZones));
    }
}
