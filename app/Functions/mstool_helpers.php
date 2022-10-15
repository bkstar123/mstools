<?php
/**
 * mstools helper functions
 *
 * @author: tuanha
 * @date: 27-June-2022
 */
if (! function_exists('toWildcardHostname')) {
    /**
     * Convert a hostname to its wildcard version
     * For instance, toWildcardHostname('www.example.com') => *.example.com
     *
     * @param  string $hostname
     * @return string
     */
    function toWildcardHostname(string $hostname)
    {
        $hostname = idn_to_ascii(trim($hostname), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        return substr_replace($hostname, '*.', 0, strpos($hostname, ".") + 1);
    }
}

if (! function_exists('convertSecondsForHuman')) {
    /**
     * Convert given number of seconds into a human readable format
     * For instance, 86420 seconds => 01 day and 20 seconds
     *
     * @param  $seconds
     * @return string
     */
    function convertSecondsForHuman($seconds)
    {
        if ($seconds < 60 && $seconds > 0) {
            return trim("$seconds " . Str::plural('second', $seconds));
        } elseif ($seconds >= 60 && $seconds < 3600) {
            $extraSeconds = convertSecondsForHuman($seconds % 60);
            $minutes = ($seconds - ($seconds % 60)) / 60;
            return trim("$minutes " . Str::plural('minute', $minutes) . " $extraSeconds");
        } elseif ($seconds >= 3600 && $seconds < 86400) {
            $extraMinutes = convertSecondsForHuman($seconds % 3600);
            $hours = ($seconds - ($seconds % 3600)) / 3600;
            return trim("$hours " . Str::plural('hour', $hours) . " $extraMinutes");
        } elseif ($seconds >= 86400) {
            $extraHours = convertSecondsForHuman($seconds % 86400);
            $days = ($seconds - ($seconds % 86400)) / 86400;
            return trim("$days " . Str::plural('day', $days) . " $extraHours");
        }
    }
}

if (! function_exists('getApexRootDomains')) {
    /**
     * Extract APEX root domains from the given array of hostnames
     * For instance, ['www.vnexpress.net'] => ['vnexpress.net']
     *
     * @param  $domains array
     * @return array
     */
    function getApexRootDomains(array $domains)
    {
        $zones = [];
        if (count($domains) > 0) {
            $TLDs = explode(',', file_get_contents(asset('/sources/tlds.txt')));
            foreach ($domains as $domain) {
                $domainParts = explode('.', trim($domain));
                $i = count($domainParts) - 1;
                $zone = $domainParts[$i];
                while ($i >= 0 && in_array($zone, $TLDs)) {
                    --$i;
                    $zone = $domainParts[$i] . '.' . $zone;
                }
                $zones[] = $zone;
            }
            $zones = array_map(function ($zone) {
                return strtolower($zone);
            }, $zones);
            $zones = array_merge([], array_unique($zones));
        }
        return $zones;
    }
}
