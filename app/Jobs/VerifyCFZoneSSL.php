<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Mail\VerifyCFZoneSSLResult;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class VerifyCFZoneSSL implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    protected $zones;

    /**
     * @var \Bkstar123\BksCMS\AdminPanel\Admin
     */
    protected $user;

    /**
     * The number of seconds the job can run before timing out
     * must be on several seconds less than the queue connection's retry_after defined in the config/queue.php
     *
     * @var int
     */
    public $timeout = 1190;

    /**
     * Create a new job instance.
     *
     * @param $zones array
     * @param $user \Bkstar123\BksCMS\AdminPanel\Admin
     * @return void
     */
    public function __construct($zones, $user)
    {
        $this->zones = $zones;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fh = fopen('php://temp', 'w');
        fputcsv($fh, [
            'URL', 'Found on Cloudflare', 'Issuer', 'SSL mode', 'SSL uploaded on', 'SSL modified on', 'Expired_at', 'Hosts', 'Note'
        ]);
        $zoneMgmt = resolve('zoneMgmt');
        $customSSL = resolve('customSSL');
        foreach ($this->zones as $index => $zone) {
            $zone = idn_to_ascii(trim($zone), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $zoneID = $zoneMgmt->getZoneID($zone);
            if ($zoneID === null || $zoneID === false) {
                fputcsv($fh, [$zone, 'false', '', '', '', '', '', '', '']);
                continue;
            }
            $currentCertID = $customSSL->getCurrentCustomCertID($zoneID);
            if ($currentCertID === false) {
                fputcsv($fh, [$zone, 'true', '', '', '', '', '', '', 'Something is unusual, pls manually verify yourself on Cloudflare portal']);
                continue;
            } elseif ($currentCertID === null) {
                fputcsv($fh, [$zone, 'true', '', '', '', '', '', '', '']);
            } else {
                $data = $customSSL->fetchCertData($zoneID, $currentCertID);
                if (!$data) {
                    fputcsv($fh, [$zone, 'true', '', '', '', '', '', '', 'Something is unusual, pls manually verify yourself on Cloudflare portal']);
                    continue;
                } else {
                    fputcsv($fh, [
                        $zone, 
                        'true', 
                        $data['issuer'],
                        $data['tls_mode'],
                        $data['uploaded_on'],
                        $data['modified_on'],
                        $data['expires_on'],
                        $data['hosts']
                    ]);
                }
            }
        }
        rewind($fh);
        Mail::to($this->user)
            ->send(new VerifyCFZoneSSLResult(stream_get_contents($fh), $this->zones));
        fclose($fh);
    }
}
