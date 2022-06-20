<?php
/**
 * RequestByUserThrottling
 *
 * Do not allow an active user to make multiple attempts against the same request during the given duration
 *
 * @author: tuanha
 * @last-mod: 18-June-2022
 */
namespace App\Http\Components;

trait GenerateCustomUniqueString
{
    /**
     * Formulate an unique cache key for the active user to the given request
     *
     * @return string
     */
    protected function generateUniqueString($extension = '')
    {
        return md5(uniqid(rand(), true)."_".getmypid()."_".gethostname()."_".time()).$extension;
    }
}
