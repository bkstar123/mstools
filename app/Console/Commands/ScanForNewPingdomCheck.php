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
        $checks = $pingdomCheck->getChecks();
        if (empty($checks)) {
            return;
        }
        if (!file_exists(storage_path('app/last_pingdom_check_id.txt'))) {
            $lastHighestCheckID = config('mstools.pingdom.reference_check_id');
        } else {
            $lastHighestCheckID = file_get_contents(storage_path('app/last_pingdom_check_id.txt')) ?? config('mstools.pingdom.reference_check_id');
        }
        $newChecks = \Arr::where($checks, function ($check) use ($lastHighestCheckID) {
            return !str_contains($check['hostname'], 'dxcloud.episerver.net') &&
                   $check['status'] == 'paused' &&
                   !array_key_exists('lasttesttime', $check) &&
                   $check['id'] > $lastHighestCheckID;
        });
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
