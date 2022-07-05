<?php
/**
 * DNSQueryable
 *
 * @author: tuanha
 * @last-mod: 04-July-2022
 */
namespace App\Http\Components;

use Exception;

trait DNSQueryable
{
    /**
     * @param string $domain
     * @return array (nested)
     */
    protected function getDNSRecords(string $domain)
    {
        $IPs = [];
        $Aliases = [];
        try {
            $a_records = dns_get_record($domain, DNS_A);
        } catch (Exception $e) {
            $a_records = [];
        }
        try {
            $cname_records = dns_get_record($domain, DNS_CNAME);
        } catch (Exception $e) {
            $cname_records = [];
        }
        if (!empty($a_records)) {
            foreach ($a_records as $record) {
                array_push($IPs, $record['ip']);
            }
        }
        if (!empty($cname_records)) {
            foreach ($cname_records as $record) {
                array_push($Aliases, $record['target']);
            }
        }
        return [
            'A' => $IPs,
            'CNAME' => $Aliases
        ];
    }
}
