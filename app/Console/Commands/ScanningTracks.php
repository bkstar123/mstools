<?php
/**
 * ScanningTracks Command
 *
 * @author: tuanha
 * @date: 08-July-2022
 */
namespace App\Console\Commands;

use App\Report;
use App\Tracking;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Http\Components\DNSQueryable;
use Illuminate\Support\Facades\Storage;
use App\Http\Components\GenerateCustomUniqueString;

class ScanningTracks extends Command
{
    use DNSQueryable, GenerateCustomUniqueString;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trackings:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan trackings for gone-live DXP sites';

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
        $trackings = Tracking::where('status', Tracking::ON)->where('tracking_size', '>', 0)->get();
        $outputFileLocation = [
            'disk' => config('mstools.report.disk'),
            'path' => config('mstools.report.directory').DIRECTORY_SEPARATOR.$this->generateUniqueString().DIRECTORY_SEPARATOR.$this->generateUniqueString('.csv')
        ];
        Storage::disk($outputFileLocation['disk'])->makeDirectory(dirname($outputFileLocation['path']));
        $fop = fopen(Storage::disk($outputFileLocation['disk'])->path($outputFileLocation['path']), 'w');
        fputcsv($fop, [
            'Site',
            'A',
            'CNAME'
        ]);
        foreach ($trackings as $tracking) {
            $goneLivedSites = $this->scan($tracking);
            foreach ($goneLivedSites as $site) {
                fputcsv($fop, [
                    $site['site'],
                    $site['A'],
                    $site['CNAME']
                ]);
            }
        }
        fclose($fop);
        Report::create([
            'name'        => 'List of DXP gone-live sites ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id'    => 1, // Fake user with username of "superadmin"
            'disk'        => $outputFileLocation['disk'],
            'path'        => $outputFileLocation['path'],
            'mime'        => 'text/csv',
            'is_public'   => true,
            'is_longlive' => true
        ]);
    }

    /**
     * Scan the given tracking for gone-live DXP sites
     *
     * @return array
     */
    protected function scan(Tracking $tracking)
    {
        $goneLivedSites = [];
        $sites = array_map(function ($site) {
            $site = idn_to_ascii(trim($site), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $dnsRecords = $this->getDNSRecords($site);
            return [
                'site'  => $site,
                'A'     => implode(',', $dnsRecords['A']),
                'CNAME' => implode(',', $dnsRecords['CNAME'])
            ];
        }, array_merge([], array_unique(explode(',', $tracking->sites))));
        $goneLivedSites = \Arr::where($sites, function ($site) {
            return (!empty($site['CNAME']) && str_contains($site['CNAME'], config('mstools.tracking.dxp')));
        });
        $remainingSites = array_merge([], array_diff(\Arr::pluck($sites, 'site'), \Arr::pluck($goneLivedSites, 'site')));
        $tracking->sites = implode(",", $remainingSites);
        $tracking->tracking_size = count($remainingSites);
        $tracking->save();
        return array_merge([], $goneLivedSites);
    }
}
