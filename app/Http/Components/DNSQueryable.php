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
     * Get DNS A, CNAME records associate with the given domains, if the $extra is set True, then it will try to fetch NS record as well
     *
     * @param string $domain
     * @param bool $extra
     * @return array (nested)
     */
    protected function getDNSRecords(string $domain, bool $extra = false)
    {
        $IPs = [];
        $Aliases = [];
        $NSs = [];
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
        if ($extra) {
            try {
                $ns_records = dns_get_record($domain, DNS_NS);
            } catch (Exception $e) {
                $ns_records = [];
            }
            if (!empty($ns_records)) {
                foreach ($ns_records as $record) {
                    array_push($NSs, $record['target']);
                }
            }
        }
        return [
            'A' => $IPs,
            'CNAME' => $Aliases,
            'NS' => $NSs
        ];
    }
}
