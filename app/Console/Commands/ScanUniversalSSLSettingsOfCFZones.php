<?php

namespace App\Console\Commands;

use App\Report;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Http\Components\GenerateCustomUniqueString;

class ScanUniversalSSLSettingsOfCFZones extends Command
{
    use GenerateCustomUniqueString;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudflare:scanUniversalSSLSettingsForZones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan Cloudflare Universal SSL settings for all zones';

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
        $outputFileLocation = [
            'disk' => config('mstools.report.disk'),
            'path' => config('mstools.report.directory').DIRECTORY_SEPARATOR.$this->generateUniqueString().DIRECTORY_SEPARATOR.$this->generateUniqueString('.csv')
        ];
        Storage::disk($outputFileLocation['disk'])->makeDirectory(dirname($outputFileLocation['path']));
        $fop = fopen(Storage::disk($outputFileLocation['disk'])->path($outputFileLocation['path']), 'w');
        fputcsv($fop, [
            'Zone', 
            'Universal SSL Enabled', 
            'CA'
        ]);
        $page = 1;
        do {
            $zones = $zoneMgmt->getPaginatedZones($page, 1000);
            if (empty($zones)) {
                break;
            }
            foreach ($zones as $zone) {
                $result = $zoneMgmt->getUniversalSSLSettingStatus($zone['id']);
                if (empty($result)) {
                    fputcsv($fop, [$zone['name'], '', '']);
                } else {
                    fputcsv($fop, [
                        $zone['name'], 
                        $result['enabled'] ? 'true' : 'false', 
                        $result['certificate_authority'] ?? ''
                    ]);
                }
            }
            ++$page;
        } while (!empty($zones));
        fclose($fop);
        Report::create([
            'name'        => 'Get Universal SSL setting status for all Cloudflare zones ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id'    => 1, // Fake user with username of "superadmin"
            'disk'        => $outputFileLocation['disk'],
            'path'        => $outputFileLocation['path'],
            'mime'        => 'text/csv',
            'is_public'   => true,
            'is_longlive' => true
        ]);
    }
}
