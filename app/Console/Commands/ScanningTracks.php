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
            'CNAME',
            'NS',
            'saas_custom_origin_server',
            'saas_ssl_type',
            'saas_ssl_status',
            'Note'
        ]);
        foreach ($trackings as $tracking) {
            $goneLivedSites = $this->scan($tracking);
            foreach ($goneLivedSites as $site) {
                fputcsv($fop, [
                    $site['site'],
                    $site['A'],
                    $site['CNAME'],
                    $site['NS'],
                    $site['saas_custom_origin_server'],
                    $site['saas_ssl_type'],
                    $site['saas_ssl_status'],
                    $site['saas_custom_origin_server'] != "N/A" && $site['saas_ssl_type'] != "dv" ? "This hostname uses custom certificate, please check further" : ''
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
        $sites = array_map(function ($site) {
            return idn_to_ascii(trim($site), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        }, array_merge([], array_unique(explode(',', $tracking->sites))));
        $cf4SaaSSites = \Arr::pluck(getOriginServerOfCF4SaasHostname($sites), 'hostname');
        $standardSites = array_merge([], array_diff($sites, $cf4SaaSSites));
        $goneLivedStandardSites = $this->scanByDNS($standardSites);
        $goneLivedCF4SaaSSites = $this->scanCF4SaaSHostnameBySSLStatus($cf4SaaSSites);
        $goneLivedSites = array_merge($goneLivedStandardSites, $goneLivedCF4SaaSSites);
        $remainingSites = array_merge([], array_diff($sites, \Arr::pluck($goneLivedSites, 'site')));
        $tracking->sites = implode(",", $remainingSites);
        $tracking->tracking_size = count($remainingSites);
        $tracking->save();
        return array_merge([], $goneLivedSites);
    }

    /**
     * Scan the given sites to detect go-live according to DNS records
     *
     * @param $sites array
     * @return array
     */
    protected function scanByDNS($sites)
    {
        $sites = array_map(function ($site) {
            $dnsRecords = $this->getDNSRecords($site, true);
            return [
                'site'  => $site,
                'A'     => implode(',', $dnsRecords['A']),
                'CNAME' => implode(',', $dnsRecords['CNAME']),
                'NS' => implode(',', $dnsRecords['NS']),
                'saas_custom_origin_server' => 'N/A',
                'saas_ssl_type' => '',
                'saas_ssl_status' => ''
            ];
        }, $sites);
        $goneLivedSites = \Arr::where($sites, function ($site) {
            return (!empty($site['CNAME']) && str_contains($site['CNAME'], config('mstools.tracking.dxp'))) ||
                   (!empty($site['NS']) && $this->validateDXPLiberateZoneNS($site['NS']));
        });
        return array_merge([], $goneLivedSites);
    }

    /**
     * Scan the given sites for CF4SaaS Hostnames that have active SSL status
     *
     * @param $sites array
     * @return array
     */
    protected function scanCF4SaaSHostnameBySSLStatus($sites)
    {
        $activeCF4SaaSHostnames = getOriginServerOfCF4SaasHostname($sites, 'active');
        $goneLivedHostnames = \Arr::where($activeCF4SaaSHostnames, function ($hostname) {
            return $hostname['ssl_status'] == 'active';
        });
        $goneLivedHostnames = array_map(function ($hostname) {
            return [
                'site'  => $hostname['hostname'],
                'A'     => '',
                'CNAME' => '',
                'NS' => '',
                'saas_custom_origin_server' => $hostname['custom_origin_server'],
                'saas_ssl_type' => $hostname['ssl_type'],
                'saas_ssl_status' => $hostname['ssl_status']
            ];
        }, $goneLivedHostnames);
        return $goneLivedHostnames;
    }

    /**
     * Validate if the NS records of a liberate zone belong to DXP NS pairs
     *
     * @param string $nsRecords
     * @return bool
     */
    protected function validateDXPLiberateZoneNS($nsRecords)
    {
        $haystack = explode(',', config('mstools.tracking.dxp_liberate_zone_ns'));
        $needle = explode(',', $nsRecords);
        if (empty(array_diff($needle, $haystack))) {
            return true;
        }
        return false;
    }
}
