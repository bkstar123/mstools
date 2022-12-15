<?php

namespace App\Console\Commands;

use App\Report;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Bkstar123\CFBuddy\Services\ZoneMgmt;
use Bkstar123\CFBuddy\Services\CustomSSL;
use App\Http\Components\GenerateCustomUniqueString;

class UniversalSSLVerificationCheck extends Command
{
    use GenerateCustomUniqueString;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'universalSSLVerification:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Universal SSL verification status for all Cloudflare zones';

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
    public function handle(ZoneMgmt $zoneMgmt, CustomSSL $customSSL)
    {
        $outputFileLocation = [
            'disk' => config('mstools.report.disk'),
            'path' => config('mstools.report.directory').DIRECTORY_SEPARATOR.$this->generateUniqueString().DIRECTORY_SEPARATOR.$this->generateUniqueString('.csv')
        ];
        Storage::disk($outputFileLocation['disk'])->makeDirectory(dirname($outputFileLocation['path']));
        $fop = fopen(Storage::disk($outputFileLocation['disk'])->path($outputFileLocation['path']), 'w');
        fputcsv($fop, [
            'Zone',
            'Hostnames with inactive universal certificate',
            'Note'
        ]);
        $page = 1;
        do {
            $zones = $zoneMgmt->getPaginatedZones($page, 100);
            if (empty($zones)) {
                break;
            }
            foreach ($zones as $zone) {
                $packs = $zoneMgmt->getUniversalSSLVerificationStatus($zone['id']);
                if (empty($packs)) {
                    fputcsv($fop, [
                        $zone['name'],
                        '',
                        'There seems no certificate packs eligible for verification on this zone'
                    ]);
                } else {
                    $inactiveUniversalSSL = [];
                    foreach ($packs as $pack) {
                        if ($pack['certificate_status'] != 'active') {
                            array_push($inactiveUniversalSSL, $pack['hostname']);
                        }
                    }
                    if (!empty($inactiveUniversalSSL)) {
                        $customCertID = $customSSL->getCurrentCustomCertID($zone['id']);
                        if (empty($customCertID)) {
                            $comment = 'No custom certificate found to cover all hostnames which are pending for renewing universal certificate';
                        } else {
                            $data = $customSSL->fetchCertData($zone['id'], $customCertID);
                            $sanDomains = (array) json_decode($data['hosts'], true);
                            $normalizedSanDomains = array_map(function ($hostname) {
                                return strtolower(idn_to_ascii(trim($hostname), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46));
                            }, $sanDomains);
                            $normalizedInactiveUniversalSSL = array_map(function ($hostname) {
                                return strtolower(idn_to_ascii(trim($hostname), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46));
                            }, $inactiveUniversalSSL);
                            $diffHostnames = array_merge([], array_diff($normalizedInactiveUniversalSSL, $normalizedSanDomains));
                            $diffHostnames = array_filter($diffHostnames, function ($hostname) use ($normalizedSanDomains) {
                                return !in_array(toWildcardHostname($hostname), $normalizedSanDomains);
                            });
                            $diffHostnames = array_merge([], $diffHostnames);
                            if (empty($diffHostnames)) {
                                $comment = "Can be safely ignored as all hostnamess are covered by a custom certificate";
                            } else {
                                $comment = "It is likely that the custom certificate on the zone does not cover following hostnames which are pending for univeral SSL renewal: " . json_encode($diffHostnames);
                            }
                        }
                    } else {
                        $comment = 'No hostnames with inactive universal certificate on the zone';
                    }
                    fputcsv($fop, [
                        $zone['name'],
                        json_encode($inactiveUniversalSSL),
                        $comment
                    ]);
                }
            }
            ++$page;
        } while (!empty($zones));
        fclose($fop);
        Report::create([
            'name'        => 'Check universal SSL verification status for all Cloudflare zones ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id'    => 1, // Fake user with username of "superadmin"
            'disk'        => $outputFileLocation['disk'],
            'path'        => $outputFileLocation['path'],
            'mime'        => 'text/csv',
            'is_public'   => true,
            'is_longlive' => true
        ]);
    }
}
