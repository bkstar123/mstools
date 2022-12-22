<?php
/**
 * Get China Network Enabled Zones on Cloudflare
 *
 * @author: tuanha
 * @date: 11-Dec-2022
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Bkstar123\BksCMS\AdminPanel\Admin;
use App\Mail\CFJDCloudZoneChangedNotify;
use App\Notifications\CloudflareJDCloudZoneChangeNotification;

class ScanForChinaNetworkZones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudflare:scanForChinaNetworkZones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan for China network activated zones on Cloudflare';

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
        $jdcloudZones = [];
        $page = 1;
        do {
            $zones = $zoneMgmt->getPaginatedZones($page, 1000);
            if (empty($zones)) {
                break;
            }
            $data = array_filter($zones, function ($zone) {
                return isset($zone['betas']) && in_array('jdcloud_network_operational', $zone['betas']);
            });
            if (!empty($data)) {
                $data = array_merge([], array_map(function ($zone) {
                    return $zone['name'];
                }, $data));
            }
            $jdcloudZones = array_merge($jdcloudZones, $data);
            ++$page;
        } while (!empty($zones));
        $contents = json_encode($jdcloudZones);
        $newHashed = md5($contents);
        if (!file_exists(storage_path('app/last_cloudflare_jdcloud_zone_hash.txt'))) {
            file_put_contents(storage_path('app/last_cloudflare_jdcloud_zone_hash.txt'), $newHashed);
            file_put_contents(storage_path('app/last_cloudflare_jdcloud_zones.txt'), $contents);
        } else {
            $currentHash = file_get_contents(storage_path('app/last_cloudflare_jdcloud_zone_hash.txt'));
            if ($newHashed != $currentHash) {
                $currentContents = json_decode(file_get_contents(storage_path('app/last_cloudflare_jdcloud_zones.txt')), true);
                $newContents = json_decode($contents, true);
                $addedZones = array_merge([], array_diff($newContents, $currentContents));
                $removedZones = array_merge([], array_diff($currentContents, $newContents));
                file_put_contents(storage_path('app/last_cloudflare_jdcloud_zone_hash.txt'), $newHashed);
                file_put_contents(storage_path('app/last_cloudflare_jdcloud_zones.txt'), $contents);
                // Send Slack Notification
                $superadmin = Admin::find(1)->first();
                if (!empty($superadmin)) {
                    $superadmin->notify(new CloudflareJDCloudZoneChangeNotification(json_encode($addedZones), json_encode($removedZones)));
                }
                $subsribers = env('CF_JDCLOUD_IP_CHANGE_SUBSCRIBER', 'tuan.hoang@optimizely.com');
                $subsribers = explode(",", $subsribers);
                foreach ($subsribers as $subsriber) {
                    \Mail::to($subsriber)->send(new CFJDCloudZoneChangedNotify(json_encode($addedZones), json_encode($removedZones)));
                }
            }
        }
    }
}
