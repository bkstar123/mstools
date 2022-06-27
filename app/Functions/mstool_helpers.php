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