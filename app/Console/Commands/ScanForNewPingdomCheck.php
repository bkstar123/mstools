<?php

namespace App\Console\Commands;

use App\Tracking;
use Illuminate\Console\Command;

class ScanForNewPingdomCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pingdom:scanForNew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan for newly created Pingdom checks which have never been unpaused yet';

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
        $pingdomCheck = resolve('pingdomCheck');
        $page = 1;
        $limit = 1000;
        $offset = 0;
        $newChecks = [];
        if (!file_exists(storage_path('app/last_pingdom_check_id.txt'))) {
            $lastHighestCheckID = config('mstools.pingdom.reference_check_id');
        } else {
            $lastHighestCheckID = file_get_contents(storage_path('app/last_pingdom_check_id.txt')) ?? config('mstools.pingdom.reference_check_id');
        }
        do {
            $checks = $pingdomCheck->getChecks($offset, $limit);
            if (empty($checks)) {
                break;
            }
            $newChecks = array_merge($newChecks, \Arr::where($checks, function ($check) use ($lastHighestCheckID) {
                return $check['id'] > $lastHighestCheckID &&
                       !str_contains($check['hostname'], config('mstools.tracking.dxp'));
            }));
            ++$page;
            $offset = ($page - 1) * $limit;
        } while (!empty($checks));
        if (!empty($newChecks)) {
            $latestCheckID = last(\Arr::sort($newChecks, function ($check) {
                return $check['id'];
            }))['id'];
            file_put_contents(storage_path('app/last_pingdom_check_id.txt'), $latestCheckID);
            Tracking::create([
                'sites'         => implode(',', \Arr::pluck($newChecks, 'hostname')),
                'admin_id'      => 1,
                'tracking_size' => count($newChecks)
            ]);
        }
    }
}
