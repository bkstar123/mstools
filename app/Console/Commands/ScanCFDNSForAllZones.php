<?php
/**
 * ScanCFDNSForAllZones
 * Depends on Mainul's script to pull all Cloudflare DNS A, CNAME records for all DXP zones
 *
 * @author tuanha
 * @date: 02 Jan 2025
 */
namespace App\Console\Commands;

use App\Report;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Http\Components\GenerateCustomUniqueString;

class ScanCFDNSForAllZones extends Command
{
    use GenerateCustomUniqueString;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudflare:scanCFDNSForAllZones 
                            {--accountName= : Provide account name, accepted values: "B2B", "DXP Customers", "Managed Services" }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan all Cloudflare zones for DNS settings';

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
        $outputFileLocation = [
            'disk' => config('mstools.report.disk'),
            'path' => config('mstools.report.directory').DIRECTORY_SEPARATOR.$this->generateUniqueString().DIRECTORY_SEPARATOR.$this->generateUniqueString('.csv')
        ];
        Storage::disk($outputFileLocation['disk'])->makeDirectory(dirname($outputFileLocation['path']));
        $location = dirname(Storage::disk($outputFileLocation['disk'])->path($outputFileLocation['path']));
        $filename = basename(Storage::disk($outputFileLocation['disk'])->path($outputFileLocation['path']));
        $apiToken= env('CF_API_TOKEN');
        $pythonExeEnv = env('MAINUL_PYTHON_EXE_ENV');
        $accountName = $this->option('accountName');
        $cmd = "curl -O https://raw.githubusercontent.com/mainulhossain123/cf_dns_extract/refs/heads/main/CF_Zone_DNS_Extraction.py && python CF_Zone_DNS_Extraction.py && rm -rf CF_Zone_DNS_Extraction.py";
        exec("docker run -it --rm -v $location:/app -e API_KEY='$apiToken' -e ACCOUNT_NAME='$accountName' -e OUTPUT_FILENAME_PREFIX=$filename $pythonExeEnv sh -c '$cmd'");
        $report = Report::create([
            'name'     => "List of CF DNS Records for all $accountName zones " . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id' => 1, // Fake user with username of "superadmin"
            'disk'     => $outputFileLocation['disk'],
            'path'     => $outputFileLocation['path'],
            'mime'     => 'text/csv',
            'is_public'   => true,
            'is_longlive' => true
        ]);
    }
}
