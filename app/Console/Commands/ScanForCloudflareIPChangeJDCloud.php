<?php
/**
 * Scan for Cloudflare IP change on China JD Cloud
 *
 * @author: tuanha
 * @date 05-Dec-2022
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\CFJDCloudIPChangeNotify;
use Bkstar123\BksCMS\AdminPanel\Admin;
use App\Notifications\CloudflareIPChangeNotification;

class ScanForCloudflareIPChangeJDCloud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudflare:scanForIPChangeOnJDCloud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan for Cloudflare IP change on China JDCloud';

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
        $cfip = resolve('cfip');
        $contents = $cfip->getCloudflareIP('jdcloud');
        if (is_array($contents) && !empty($contents)) {
            $newHashed = $contents['etag'];
            $contents = json_encode($contents);
            if (!file_exists(storage_path('app/last_cloudflare_ips_hash.txt'))) {
                file_put_contents(storage_path('app/last_cloudflare_ips_hash.txt'), $newHashed);
                file_put_contents(storage_path('app/last_cloudflare_ips.txt'), $contents);
            } else {
                $currentHash = file_get_contents(storage_path('app/last_cloudflare_ips_hash.txt'));
                if ($newHashed != $currentHash) {
                    $currentContents = json_decode(file_get_contents(storage_path('app/last_cloudflare_ips.txt')), true);
                    $newContents = json_decode($contents, true);
                    $addedIPs = [
                        'ipv4_cidrs' => array_merge([], array_diff($newContents['ipv4_cidrs'], $currentContents['ipv4_cidrs'])),
                        'ipv6_cidrs' => array_merge([], array_diff($newContents['ipv6_cidrs'], $currentContents['ipv6_cidrs'])),
                        'jdcloud_cidrs' => array_merge([], array_diff($newContents['jdcloud_cidrs'], $currentContents['jdcloud_cidrs']))
                    ];
                    $removedIPs = [
                        'ipv4_cidrs' => array_merge([], array_diff($currentContents['ipv4_cidrs'], $newContents['ipv4_cidrs'])),
                        'ipv6_cidrs' => array_merge([], array_diff($currentContents['ipv6_cidrs'], $newContents['ipv6_cidrs'])),
                        'jdcloud_cidrs' => array_merge([], array_diff($currentContents['jdcloud_cidrs'], $newContents['jdcloud_cidrs']))
                    ];
                    file_put_contents(storage_path('app/last_cloudflare_ips_hash.txt'), $newHashed);
                    file_put_contents(storage_path('app/last_cloudflare_ips.txt'), $contents);
                    // Send Slack Notification
                    $superadmin = Admin::find(1)->first();
                    if (!empty($superadmin)) {
                        $superadmin->notify(new CloudflareIPChangeNotification(json_encode($addedIPs), json_encode($removedIPs)));
                    }
                    $subsribers = env('CF_JDCLOUD_IP_CHANGE_SUBSCRIBER', 'tuan.hoang@optimizely.com');
                    $subsribers = explode(",", $subsribers);
                    foreach ($subsribers as $subsriber) {
                        \Mail::to($subsriber)->send(new CFJDCloudIPChangeNotify(json_encode($addedIPs), json_encode($removedIPs)));
                    }
                }
            }
        }
    }
}
